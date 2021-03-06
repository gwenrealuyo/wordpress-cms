<?php

declare (strict_types=1);
namespace PoP\ComponentModel\DirectiveResolvers;

use Exception;
use PrefixedByPoP\League\Pipeline\StageInterface;
use PoP\ComponentModel\AttachableExtensions\AttachableExtensionTrait;
use PoP\ComponentModel\ComponentConfiguration;
use PoP\ComponentModel\DirectivePipeline\DirectivePipelineUtils;
use PoP\ComponentModel\Directives\DirectiveTypes;
use PoP\ComponentModel\Environment;
use PoP\ComponentModel\Facades\HelperServices\SemverHelperServiceFacade;
use PoP\ComponentModel\Facades\Instances\InstanceManagerFacade;
use PoP\ComponentModel\Facades\Schema\FeedbackMessageStoreFacade;
use PoP\ComponentModel\Facades\Schema\FieldQueryInterpreterFacade;
use PoP\ComponentModel\Feedback\Tokens;
use PoP\ComponentModel\HelperServices\SemverHelperServiceInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Resolvers\FieldOrDirectiveResolverTrait;
use PoP\ComponentModel\Resolvers\ResolverTypes;
use PoP\ComponentModel\Resolvers\WithVersionConstraintFieldOrDirectiveResolverTrait;
use PoP\ComponentModel\Schema\FeedbackMessageStoreInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\State\ApplicationState;
use PoP\ComponentModel\TypeResolvers\FieldSymbols;
use PoP\ComponentModel\TypeResolvers\PipelinePositions;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\Versioning\VersioningHelpers;
use PoP\FieldQuery\QueryHelpers;
use PoP\Hooks\Facades\HooksAPIFacade;
use PoP\Hooks\HooksAPIInterface;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\Translation\TranslationAPIInterface;
abstract class AbstractDirectiveResolver implements \PoP\ComponentModel\DirectiveResolvers\DirectiveResolverInterface, \PoP\ComponentModel\DirectiveResolvers\SchemaDirectiveResolverInterface, StageInterface
{
    use AttachableExtensionTrait;
    use RemoveIDsDataFieldsDirectiveResolverTrait;
    use FieldOrDirectiveResolverTrait;
    use WithVersionConstraintFieldOrDirectiveResolverTrait;
    const MESSAGE_EXPRESSIONS = 'expressions';
    /**
     * @var string
     */
    protected $directive;
    /**
     * @var \PoP\Translation\TranslationAPIInterface
     */
    protected $translationAPI;
    /**
     * @var \PoP\Hooks\HooksAPIInterface
     */
    protected $hooksAPI;
    /**
     * @var \PoP\ComponentModel\Instances\InstanceManagerInterface
     */
    protected $instanceManager;
    /**
     * @var \PoP\ComponentModel\Schema\FieldQueryInterpreterInterface
     */
    protected $fieldQueryInterpreter;
    /**
     * @var \PoP\ComponentModel\Schema\FeedbackMessageStoreInterface
     */
    protected $feedbackMessageStore;
    /**
     * @var \PoP\ComponentModel\HelperServices\SemverHelperServiceInterface
     */
    protected $semverHelperService;
    /**
     * @var array<string, mixed>
     */
    protected $directiveArgsForSchema = [];
    /**
     * @var array<string, mixed>
     */
    protected $directiveArgsForResultItems = [];
    /**
     * @var array[]
     */
    protected $nestedDirectivePipelineData = [];
    /**
     * @var array<string, array>
     */
    protected $schemaDefinitionForDirectiveCache = [];
    /**
     * The directiveResolvers are NOT instantiated through the service container!
     *
     * Instead, the directive will be instantiated in AbstractTypeResolver:
     *   new $directiveClass($fieldDirective)
     *
     * Whenever having depended-upon services, these can be obtained like this (or via a Facade):
     *   $instanceManager->getInstance(...)
     *
     * DirectiveResolvers must still be added to schema-services.yml, though.
     * This is because they need to be registered, so that all directives
     * can be displayed in the GraphQL API's Access Control Lists
     */
    public function __construct(?string $directive = null)
    {
        // If the directive is not provided, then it directly the directive name
        // This allows to instantiate the directive through the DependencyInjection component
        $this->directive = $directive ?? $this->getDirectiveName();
        // Obtain these services directly from the container, instead of using autowiring
        $this->translationAPI = TranslationAPIFacade::getInstance();
        $this->hooksAPI = HooksAPIFacade::getInstance();
        $this->instanceManager = InstanceManagerFacade::getInstance();
        $this->fieldQueryInterpreter = FieldQueryInterpreterFacade::getInstance();
        $this->feedbackMessageStore = FeedbackMessageStoreFacade::getInstance();
        $this->semverHelperService = SemverHelperServiceFacade::getInstance();
    }
    /**
     * Directives can be either of type "Schema" or "Query" and,
     * depending on one case or the other, might be exposed to the user.
     * By default, use the Query type
     */
    public function getDirectiveType() : string
    {
        return DirectiveTypes::QUERY;
    }
    /**
     * If a directive does not operate over the resultItems, then it must not allow to add fields or dynamic values in the directive arguments
     * Otherwise, it can lead to errors, since the field would never be transformed/casted to the expected type
     * Eg: <cacheControl(maxAge:id())>
     */
    protected function disableDynamicFieldsFromDirectiveArgs() : bool
    {
        return \false;
    }
    public function dissectAndValidateDirectiveForSchema(TypeResolverInterface $typeResolver, array &$fieldDirectiveFields, array &$variables, array &$schemaErrors, array &$schemaWarnings, array &$schemaDeprecations, array &$schemaNotices, array &$schemaTraces) : array
    {
        // If it has nestedDirectives, extract them and validate them
        $nestedFieldDirectives = $this->fieldQueryInterpreter->getFieldDirectives($this->directive, \false);
        if ($nestedFieldDirectives) {
            $nestedDirectiveSchemaErrors = $nestedDirectiveSchemaWarnings = $nestedDirectiveSchemaDeprecations = $nestedDirectiveSchemaNotices = $nestedDirectiveSchemaTraces = [];
            $nestedFieldDirectives = QueryHelpers::splitFieldDirectives($nestedFieldDirectives);
            // Support repeated fields by adding a counter next to them
            if (\count($nestedFieldDirectives) != \count(\array_unique($nestedFieldDirectives))) {
                // Find the repeated fields, and add a counter next to them
                $expandedNestedFieldDirectives = [];
                $counters = [];
                foreach ($nestedFieldDirectives as $nestedFieldDirective) {
                    if (!isset($counters[$nestedFieldDirective])) {
                        $expandedNestedFieldDirectives[] = $nestedFieldDirective;
                        $counters[$nestedFieldDirective] = 1;
                    } else {
                        $expandedNestedFieldDirectives[] = $nestedFieldDirective . FieldSymbols::REPEATED_DIRECTIVE_COUNTER_SEPARATOR . $counters[$nestedFieldDirective];
                        $counters[$nestedFieldDirective]++;
                    }
                }
                $nestedFieldDirectives = $expandedNestedFieldDirectives;
            }
            // Each composed directive will deal with the same fields as the current directive
            $nestedFieldDirectiveFields = $fieldDirectiveFields;
            foreach ($nestedFieldDirectives as $nestedFieldDirective) {
                $nestedFieldDirectiveFields[$nestedFieldDirective] = $fieldDirectiveFields[$this->directive];
            }
            $this->nestedDirectivePipelineData = $typeResolver->resolveDirectivesIntoPipelineData($nestedFieldDirectives, $nestedFieldDirectiveFields, \true, $variables, $nestedDirectiveSchemaErrors, $nestedDirectiveSchemaWarnings, $nestedDirectiveSchemaDeprecations, $nestedDirectiveSchemaNotices, $nestedDirectiveSchemaTraces);
            foreach ($nestedDirectiveSchemaDeprecations as $nestedDirectiveSchemaDeprecation) {
                \array_unshift($nestedDirectiveSchemaDeprecation[Tokens::PATH], $this->directive);
                $schemaDeprecations[] = $nestedDirectiveSchemaDeprecation;
            }
            foreach ($nestedDirectiveSchemaWarnings as $nestedDirectiveSchemaWarning) {
                \array_unshift($nestedDirectiveSchemaWarning[Tokens::PATH], $this->directive);
                $schemaWarnings[] = $nestedDirectiveSchemaWarning;
            }
            foreach ($nestedDirectiveSchemaNotices as $nestedDirectiveSchemaNotice) {
                \array_unshift($nestedDirectiveSchemaNotice[Tokens::PATH], $this->directive);
                $schemaNotices[] = $nestedDirectiveSchemaNotice;
            }
            foreach ($nestedDirectiveSchemaTraces as $nestedDirectiveSchemaTrace) {
                \array_unshift($nestedDirectiveSchemaTrace[Tokens::PATH], $this->directive);
                $schemaTraces[] = $nestedDirectiveSchemaTrace;
            }
            // If there is any error, then we also can't proceed with the current directive.
            // Throw an error for this level, and underlying errors as nested
            if ($nestedDirectiveSchemaErrors) {
                $schemaError = [Tokens::PATH => [$this->directive], Tokens::MESSAGE => $this->translationAPI->__('This directive can\'t be executed due to errors from its composed directives', 'component-model')];
                foreach ($nestedDirectiveSchemaErrors as $nestedDirectiveSchemaError) {
                    \array_unshift($nestedDirectiveSchemaError[Tokens::PATH], $this->directive);
                    $this->prependPathOnNestedErrors($nestedDirectiveSchemaError);
                    $schemaError[Tokens::EXTENSIONS][Tokens::NESTED][] = $nestedDirectiveSchemaError;
                }
                $schemaErrors[] = $schemaError;
                return [
                    null,
                    // $validDirective
                    null,
                    // $directiveName
                    null,
                ];
            }
        }
        // First validate schema (eg of error in schema: ?query=posts<include(if:this-field-doesnt-exist())>)
        list($validDirective, $directiveName, $directiveArgs, $directiveSchemaErrors, $directiveSchemaWarnings, $directiveSchemaDeprecations) = $this->fieldQueryInterpreter->extractDirectiveArgumentsForSchema($this, $typeResolver, $this->directive, $variables, $this->disableDynamicFieldsFromDirectiveArgs());
        // Store the args, they may be used in `resolveDirective`
        $this->directiveArgsForSchema = $directiveArgs;
        // If there were errors, warning or deprecations, integrate them into the feedback objects
        $schemaErrors = \array_merge($schemaErrors, $directiveSchemaErrors);
        $schemaWarnings = \array_merge($schemaWarnings, $directiveSchemaWarnings);
        $schemaDeprecations = \array_merge($schemaDeprecations, $directiveSchemaDeprecations);
        return [$validDirective, $directiveName, $directiveArgs];
    }
    /**
     * Add the directive to the head of the error path, for all nested errors
     */
    protected function prependPathOnNestedErrors(array &$nestedDirectiveSchemaError) : void
    {
        if (isset($nestedDirectiveSchemaError[Tokens::EXTENSIONS][Tokens::NESTED])) {
            foreach ($nestedDirectiveSchemaError[Tokens::EXTENSIONS][Tokens::NESTED] as &$deeplyNestedDirectiveSchemaError) {
                \array_unshift($deeplyNestedDirectiveSchemaError[Tokens::PATH], $this->directive);
                $this->prependPathOnNestedErrors($deeplyNestedDirectiveSchemaError);
            }
        }
    }
    /**
     * By default, validate if there are deprecated fields
     */
    public function validateDirectiveArgumentsForSchema(TypeResolverInterface $typeResolver, string $directiveName, array $directiveArgs, array &$schemaErrors, array &$schemaWarnings, array &$schemaDeprecations) : array
    {
        if ($maybeDeprecation = $this->resolveSchemaDirectiveDeprecationDescription($typeResolver, $directiveName, $directiveArgs)) {
            $schemaDeprecations[] = [Tokens::PATH => [$this->directive], Tokens::MESSAGE => $maybeDeprecation];
        }
        return $directiveArgs;
    }
    /**
     * @param object $resultItem
     */
    public function dissectAndValidateDirectiveForResultItem(TypeResolverInterface $typeResolver, $resultItem, array &$variables, array &$expressions, array &$dbErrors, array &$dbWarnings, array &$dbDeprecations) : array
    {
        list($validDirective, $directiveName, $directiveArgs, $nestedDBErrors, $nestedDBWarnings) = $this->fieldQueryInterpreter->extractDirectiveArgumentsForResultItem($this, $typeResolver, $resultItem, $this->directive, $variables, $expressions);
        // Store the args, they may be used in `resolveDirective`
        $this->directiveArgsForResultItems[$typeResolver->getID($resultItem)] = $directiveArgs;
        if ($nestedDBWarnings || $nestedDBErrors) {
            foreach ($nestedDBErrors as $id => $fieldOutputKeyErrorMessages) {
                $dbErrors[$id] = \array_merge($dbErrors[$id] ?? [], $fieldOutputKeyErrorMessages);
            }
            foreach ($nestedDBWarnings as $id => $fieldOutputKeyWarningMessages) {
                $dbWarnings[$id] = \array_merge($dbWarnings[$id] ?? [], $fieldOutputKeyWarningMessages);
            }
        }
        return [$validDirective, $directiveName, $directiveArgs];
    }
    /**
     * Indicate to what fieldNames this directive can be applied.
     * Returning an empty array means all of them
     */
    public function getFieldNamesToApplyTo() : array
    {
        // By default, apply to all fieldNames
        return [];
    }
    /**
     * Define if to use the version to decide if to process the directive or not
     */
    public function decideCanProcessBasedOnVersionConstraint(TypeResolverInterface $typeResolver) : bool
    {
        return \false;
    }
    /**
     * By default, the directiveResolver instance can process the directive
     * This function can be overriden to force certain value on the directive args before it can be executed
     */
    public function resolveCanProcess(TypeResolverInterface $typeResolver, string $directiveName, array $directiveArgs, string $field, array &$variables) : bool
    {
        /** Check if to validate the version */
        if (Environment::enableSemanticVersionConstraints() && $this->decideCanProcessBasedOnVersionConstraint($typeResolver)) {
            /**
             * Please notice: we can get the fieldVersion directly from this instance,
             * and not from the schemaDefinition, because the version is set at the FieldResolver level,
             * and not the FieldInterfaceResolver, which is the other entity filling data
             * inside the schemaDefinition object.
             * If this directive is tagged with a version...
             */
            if ($schemaDirectiveVersion = $this->getSchemaDirectiveVersion($typeResolver)) {
                $vars = ApplicationState::getVars();
                /**
                 * Get versionConstraint in this order:
                 * 1. Passed as directive argument
                 * 2. Through param `directiveVersionConstraints[$directiveName]`: specific to the directive
                 * 3. Through param `versionConstraint`: applies to all fields and directives in the query
                 */
                $versionConstraint = $directiveArgs[SchemaDefinition::ARGNAME_VERSION_CONSTRAINT] ?? VersioningHelpers::getVersionConstraintsForDirective($this->getDirectiveName()) ?? $vars['version-constraint'];
                /**
                 * If the query doesn't restrict the version, then do not process
                 */
                if (!$versionConstraint) {
                    return \false;
                }
                /**
                 * Compare using semantic versioning constraint rules, as used by Composer
                 * If passing a wrong value to validate against (eg: "saraza" instead of "1.0.0"), it will throw an Exception
                 */
                try {
                    return $this->semverHelperService->satisfies($schemaDirectiveVersion, $versionConstraint);
                } catch (Exception $exception) {
                    return \false;
                }
            }
        }
        return \true;
    }
    public function resolveSchemaValidationErrorDescriptions(TypeResolverInterface $typeResolver, string $directiveName, array $directiveArgs = []) : ?array
    {
        $directiveSchemaDefinition = $this->getSchemaDefinitionForDirective($typeResolver);
        if ($directiveArgsSchemaDefinition = $directiveSchemaDefinition[SchemaDefinition::ARGNAME_ARGS] ?? null) {
            /**
             * Validate mandatory values
             */
            if ($maybeError = $this->maybeValidateNotMissingFieldOrDirectiveArguments($typeResolver, $directiveName, $directiveArgs, $directiveArgsSchemaDefinition, ResolverTypes::DIRECTIVE)) {
                return [$maybeError];
            }
            if ($this->canValidateFieldOrDirectiveArgumentsWithValuesForSchema($directiveArgs)) {
                /**
                 * Validate array types are provided as arrays
                 */
                if ($maybeError = $this->maybeValidateArrayTypeFieldOrDirectiveArguments($typeResolver, $directiveName, $directiveArgs, $directiveArgsSchemaDefinition, ResolverTypes::DIRECTIVE)) {
                    return [$maybeError];
                }
                /**
                 * Validate enums
                 */
                list($maybeError) = $this->maybeValidateEnumFieldOrDirectiveArguments($typeResolver, $directiveName, $directiveArgs, $directiveArgsSchemaDefinition, ResolverTypes::DIRECTIVE);
                if ($maybeError) {
                    return [$maybeError];
                }
            }
        }
        // Custom validations
        return $this->doResolveSchemaValidationErrorDescriptions($typeResolver, $directiveName, $directiveArgs);
    }
    /**
     * Custom validations. Function to override
     */
    protected function doResolveSchemaValidationErrorDescriptions(TypeResolverInterface $typeResolver, string $directiveName, array $directiveArgs = []) : ?array
    {
        return null;
    }
    /**
     * @return mixed[]
     * @param int|string $id
     */
    protected function getExpressionsForResultItem($id, array &$variables, array &$messages) : array
    {
        // Create a custom $variables containing all the properties from $dbItems for this resultItem
        // This way, when encountering $propName in a fieldArg in a fieldResolver, it can resolve that value
        // Otherwise it can't, since the fieldResolver doesn't have access to either $dbItems
        return \array_merge($variables, $messages[self::MESSAGE_EXPRESSIONS][(string) $id] ?? []);
    }
    /**
     * @param int|string $id
     * @param mixed $value
     */
    protected function addExpressionForResultItem($id, string $key, $value, array &$messages) : void
    {
        $messages[self::MESSAGE_EXPRESSIONS][(string) $id][$key] = $value;
    }
    /**
     * @param int|string $id
     * @return mixed
     */
    protected function getExpressionForResultItem($id, string $key, array &$messages)
    {
        return $messages[self::MESSAGE_EXPRESSIONS][(string) $id][$key] ?? null;
    }
    /**
     * By default, place the directive after the ResolveAndMerge directive, so the property will be in $dbItems by then
     */
    public function getPipelinePosition() : string
    {
        return PipelinePositions::AFTER_RESOLVE;
    }
    /**
     * By default, a directive can be executed only one time for "Schema" and "System"
     * type directives (eg: <translate(en,es),translate(es,en)>),
     * and many times for the other types, "Query", "Scripting" and "Indexing"
     */
    public function isRepeatable() : bool
    {
        return !($this->getDirectiveType() == DirectiveTypes::SYSTEM || $this->getDirectiveType() == DirectiveTypes::SCHEMA);
    }
    /**
     * Indicate if the directive needs to be passed $idsDataFields filled with data to be able to execute
     * Because most commonly it will need, the default value is `true`
     */
    public function needsIDsDataFieldsToExecute() : bool
    {
        return \true;
    }
    /**
     * Indicate that there is data in variable $idsDataFields
     */
    protected function hasIDsDataFields(array &$idsDataFields) : bool
    {
        foreach ($idsDataFields as $id => &$data_fields) {
            if ($data_fields['direct'] ?? null) {
                // If there's data-fields to fetch for any ID, that's it, there's data
                return \true;
            }
        }
        // If we reached here, there is no data
        return \false;
    }
    public function getSchemaDirectiveVersion(TypeResolverInterface $typeResolver) : ?string
    {
        return null;
    }
    public function enableOrderedSchemaDirectiveArgs(TypeResolverInterface $typeResolver) : bool
    {
        if ($schemaDefinitionResolver = $this->getSchemaDefinitionResolver($typeResolver)) {
            return $schemaDefinitionResolver->enableOrderedSchemaDirectiveArgs($typeResolver);
        }
        return \true;
    }
    public function getSchemaDirectiveArgs(TypeResolverInterface $typeResolver) : array
    {
        if ($schemaDefinitionResolver = $this->getSchemaDefinitionResolver($typeResolver)) {
            return $schemaDefinitionResolver->getSchemaDirectiveArgs($typeResolver);
        }
        return [];
    }
    /**
     * Processes the directive args:
     *
     * 1. Adds the version constraint (if enabled)
     * 2. Places all entries under their own name
     * 3. If any entry has no name, it is skipped
     *
     * @return array<string, array>
     */
    protected function getFilteredSchemaDirectiveArgs(TypeResolverInterface $typeResolver, array $schemaDirectiveArgs) : array
    {
        $this->maybeAddVersionConstraintSchemaFieldOrDirectiveArg($schemaDirectiveArgs, !empty($this->getSchemaDirectiveVersion($typeResolver)));
        // Add the args under their name. Watch out: the name is mandatory!
        // If it hasn't been set, then skip the entry
        $schemaDirectiveArgsByName = [];
        foreach ($schemaDirectiveArgs as $arg) {
            if (!isset($arg[SchemaDefinition::ARGNAME_NAME])) {
                continue;
            }
            $schemaDirectiveArgsByName[$arg[SchemaDefinition::ARGNAME_NAME]] = $arg;
        }
        return $schemaDirectiveArgsByName;
    }
    public function getSchemaDirectiveDeprecationDescription(TypeResolverInterface $typeResolver) : ?string
    {
        return ($getSchemaDefinitionResolver = $this->getSchemaDefinitionResolver($typeResolver)) ? $getSchemaDefinitionResolver->getSchemaDirectiveDeprecationDescription($typeResolver) : null;
    }
    public function resolveSchemaDirectiveDeprecationDescription(TypeResolverInterface $typeResolver, string $directiveName, array $directiveArgs = []) : ?string
    {
        $directiveSchemaDefinition = $this->getSchemaDefinitionForDirective($typeResolver);
        if ($directiveArgsSchemaDefinition = $directiveSchemaDefinition[SchemaDefinition::ARGNAME_ARGS] ?? null) {
            list($maybeError, $maybeDeprecation) = $this->maybeValidateEnumFieldOrDirectiveArguments($typeResolver, $directiveName, $directiveArgs, $directiveArgsSchemaDefinition, ResolverTypes::DIRECTIVE);
            if ($maybeDeprecation) {
                return $maybeDeprecation;
            }
        }
        return null;
    }
    public function getSchemaDirectiveWarningDescription(TypeResolverInterface $typeResolver) : ?string
    {
        if ($schemaDefinitionResolver = $this->getSchemaDefinitionResolver($typeResolver)) {
            return $schemaDefinitionResolver->getSchemaDirectiveWarningDescription($typeResolver);
        }
        return null;
    }
    public function resolveSchemaDirectiveWarningDescription(TypeResolverInterface $typeResolver) : ?string
    {
        if (Environment::enableSemanticVersionConstraints()) {
            /**
             * If restricting the version, and this fieldResolver doesn't have any version, then show a warning
             */
            if ($versionConstraint = $this->directiveArgsForSchema[SchemaDefinition::ARGNAME_VERSION_CONSTRAINT] ?? null) {
                /**
                 * If this fieldResolver doesn't have versioning, then it accepts everything
                 */
                if (!$this->decideCanProcessBasedOnVersionConstraint($typeResolver)) {
                    return \sprintf($this->translationAPI->__('The DirectiveResolver used to process directive \'%s\' (which has version \'%s\') does not pay attention to the version constraint; hence, argument \'versionConstraint\', with value \'%s\', was ignored', 'component-model'), $this->getDirectiveName(), $this->getSchemaDirectiveVersion($typeResolver) ?? '', $versionConstraint);
                }
            }
        }
        return $this->getSchemaDirectiveWarningDescription($typeResolver);
    }
    public function getSchemaDirectiveExpressions(TypeResolverInterface $typeResolver) : array
    {
        if ($schemaDefinitionResolver = $this->getSchemaDefinitionResolver($typeResolver)) {
            return $schemaDefinitionResolver->getSchemaDirectiveExpressions($typeResolver);
        }
        return [];
    }
    public function getSchemaDirectiveDescription(TypeResolverInterface $typeResolver) : ?string
    {
        if ($schemaDefinitionResolver = $this->getSchemaDefinitionResolver($typeResolver)) {
            return $schemaDefinitionResolver->getSchemaDirectiveDescription($typeResolver);
        }
        return null;
    }
    public function isGlobal(TypeResolverInterface $typeResolver) : bool
    {
        if ($schemaDefinitionResolver = $this->getSchemaDefinitionResolver($typeResolver)) {
            return $schemaDefinitionResolver->isGlobal($typeResolver);
        }
        return \false;
    }
    public function __invoke($payload)
    {
        // 1. Extract the arguments from the payload
        // $pipelineIDsDataFields is an array containing all stages of the pipe
        // The one corresponding to the current stage is at the head. Take it out from there,
        // and keep passing down the rest of the array to the next stages
        list($typeResolver, $pipelineIDsDataFields, $pipelineDirectiveResolverInstances, $resultIDItems, $unionDBKeyIDs, $dbItems, $previousDBItems, $variables, $messages, $dbErrors, $dbWarnings, $dbDeprecations, $dbNotices, $dbTraces, $schemaErrors, $schemaWarnings, $schemaDeprecations, $schemaNotices, $schemaTraces) = DirectivePipelineUtils::extractArgumentsFromPayload($payload);
        // Extract the head, keep passing down the rest
        $idsDataFields = $pipelineIDsDataFields[0];
        \array_shift($pipelineIDsDataFields);
        // The $pipelineDirectiveResolverInstances is the series of directives executed in the pipeline
        // The current stage is at the head. Remove it
        \array_shift($pipelineDirectiveResolverInstances);
        // // 2. Validate operation
        // $this->validateDirective(
        //     $typeResolver,
        //     $idsDataFields,
        //     $pipelineIDsDataFields,
        //     $pipelineDirectiveResolverInstances,
        //     $resultIDItems,
        //     $dbItems,
        //     $previousDBItems,
        //     $variables,
        //     $messages,
        //     $dbErrors,
        //     $dbWarnings,
        //     $dbDeprecations,
        //     $dbNotices,
        //     $dbTraces,
        //     $schemaErrors,
        //     $schemaWarnings,
        //     $schemaDeprecations,
        //     $schemaNotices,
        //     $schemaTraces
        // );
        // 2. Execute operation.
        // First check that if the validation took away the elements, and so the directive can't execute anymore
        // For instance, executing ?query=posts.id|title<default,translate(from:en,to:es)> will fail
        // after directive "default", so directive "translate" must not even execute
        if (!$this->needsIDsDataFieldsToExecute() || $this->hasIDsDataFields($idsDataFields)) {
            // If the directive resolver throws an Exception,
            // catch it and add dbErrors
            try {
                $this->resolveDirective($typeResolver, $idsDataFields, $pipelineIDsDataFields, $pipelineDirectiveResolverInstances, $resultIDItems, $unionDBKeyIDs, $dbItems, $previousDBItems, $variables, $messages, $dbErrors, $dbWarnings, $dbDeprecations, $dbNotices, $dbTraces, $schemaErrors, $schemaWarnings, $schemaDeprecations, $schemaNotices, $schemaTraces);
            } catch (Exception $e) {
                $failureMessage = \sprintf($this->translationAPI->__('Resolving directive \'%s\' produced an exception, with message: \'%s\'', 'component-model'), $this->directive, $e->getMessage());
                $this->processFailure($failureMessage, [], $idsDataFields, $pipelineIDsDataFields, $dbItems, $dbErrors, $dbWarnings);
            }
        }
        // 3. Re-create the payload from the modified variables
        return DirectivePipelineUtils::convertArgumentsToPayload($typeResolver, $pipelineIDsDataFields, $pipelineDirectiveResolverInstances, $resultIDItems, $unionDBKeyIDs, $dbItems, $previousDBItems, $variables, $messages, $dbErrors, $dbWarnings, $dbDeprecations, $dbNotices, $dbTraces, $schemaErrors, $schemaWarnings, $schemaDeprecations, $schemaNotices, $schemaTraces);
    }
    /**
     * Depending on environment configuration, either show a warning,
     * or show an error and remove the fields from the directive pipeline for further execution
     */
    protected function processFailure(string $failureMessage, array $failedFields, array &$idsDataFields, array &$succeedingPipelineIDsDataFields, array &$dbItems, array &$dbErrors, array &$dbWarnings) : void
    {
        $allFieldsFailed = empty($failedFields);
        if ($allFieldsFailed) {
            // Remove all fields
            $idsDataFieldsToRemove = $idsDataFields;
            // Calculate which fields are being removed, to add to the error
            foreach ($idsDataFields as $id => &$data_fields) {
                $failedFields = \array_merge($failedFields, $data_fields['direct']);
            }
            $failedFields = \array_values(\array_unique($failedFields));
        } else {
            $idsDataFieldsToRemove = [];
            // Calculate which fields to remove
            foreach ($idsDataFields as $id => &$data_fields) {
                $idsDataFieldsToRemove[(string) $id]['direct'] = \array_intersect($data_fields['direct'], $failedFields);
            }
        }
        // If the failure must be processed as an error, we must also remove the fields from the directive pipeline
        $removeFieldIfDirectiveFailed = ComponentConfiguration::removeFieldIfDirectiveFailed();
        if ($removeFieldIfDirectiveFailed) {
            $this->removeIDsDataFields($idsDataFieldsToRemove, $succeedingPipelineIDsDataFields);
        }
        $setFailingFieldResponseAsNull = ComponentConfiguration::setFailingFieldResponseAsNull();
        if ($setFailingFieldResponseAsNull) {
            $this->setIDsDataFieldsAsNull($idsDataFieldsToRemove, $dbItems);
        }
        // Show the failureMessage either as error or as warning
        $directiveName = $this->getDirectiveName();
        if ($setFailingFieldResponseAsNull) {
            foreach ($idsDataFieldsToRemove as $id => $dataFields) {
                foreach ($dataFields['direct'] as $failedField) {
                    $dbErrors[(string) $id][] = [Tokens::PATH => [$failedField, $this->directive], Tokens::MESSAGE => $failureMessage];
                }
            }
        } elseif ($removeFieldIfDirectiveFailed) {
            if (\count($failedFields) == 1) {
                $message = $this->translationAPI->__('%s. Field \'%s\' has been removed from the directive pipeline', 'component-model');
            } else {
                $message = $this->translationAPI->__('%s. Fields \'%s\' have been removed from the directive pipeline', 'component-model');
            }
            foreach ($idsDataFieldsToRemove as $id => $dataFields) {
                foreach ($dataFields['direct'] as $failedField) {
                    $dbErrors[(string) $id][] = [Tokens::PATH => [$failedField, $this->directive], Tokens::MESSAGE => \sprintf($message, $failureMessage, \implode($this->translationAPI->__('\', \''), $failedFields))];
                }
            }
        } else {
            if (\count($failedFields) === 1) {
                $message = $this->translationAPI->__('%s. Execution of directive \'%s\' has been ignored on field \'%s\'', 'component-model');
            } else {
                $message = $this->translationAPI->__('%s. Execution of directive \'%s\' has been ignored on fields \'%s\'', 'component-model');
            }
            foreach ($idsDataFieldsToRemove as $id => $dataFields) {
                foreach ($dataFields['direct'] as $failedField) {
                    $dbWarnings[(string) $id][] = [Tokens::PATH => [$failedField, $this->directive], Tokens::MESSAGE => \sprintf($message, $failureMessage, $directiveName, \implode($this->translationAPI->__('\', \''), $failedFields))];
                }
            }
        }
    }
    public function getSchemaDefinitionResolver(TypeResolverInterface $typeResolver) : ?\PoP\ComponentModel\DirectiveResolvers\SchemaDirectiveResolverInterface
    {
        return null;
    }
    /**
     * Directives may not be directly visible in the schema,
     * eg: because their name is duplicated across directives (eg: "cacheControl")
     * or because they are used through code (eg: "validateIsUserLoggedIn")
     */
    public function skipAddingToSchemaDefinition() : bool
    {
        return \false;
    }
    public function getSchemaDefinitionForDirective(TypeResolverInterface $typeResolver) : array
    {
        // First check if the value was cached
        $key = $typeResolver->getNamespacedTypeName();
        if (!isset($this->schemaDefinitionForDirectiveCache[$key])) {
            $directiveName = $this->getDirectiveName();
            $schemaDefinition = [SchemaDefinition::ARGNAME_NAME => $directiveName, SchemaDefinition::ARGNAME_DIRECTIVE_TYPE => $this->getDirectiveType(), SchemaDefinition::ARGNAME_DIRECTIVE_PIPELINE_POSITION => $this->getPipelinePosition(), SchemaDefinition::ARGNAME_DIRECTIVE_IS_REPEATABLE => $this->isRepeatable(), SchemaDefinition::ARGNAME_DIRECTIVE_NEEDS_DATA_TO_EXECUTE => $this->needsIDsDataFieldsToExecute()];
            if ($limitedToFields = $this->getFieldNamesToApplyTo()) {
                $schemaDefinition[SchemaDefinition::ARGNAME_DIRECTIVE_LIMITED_TO_FIELDS] = $limitedToFields;
            }
            if ($schemaDefinitionResolver = $this->getSchemaDefinitionResolver($typeResolver)) {
                if ($description = $schemaDefinitionResolver->getSchemaDirectiveDescription($typeResolver)) {
                    $schemaDefinition[SchemaDefinition::ARGNAME_DESCRIPTION] = $description;
                }
                if ($expressions = $schemaDefinitionResolver->getSchemaDirectiveExpressions($typeResolver)) {
                    $schemaDefinition[SchemaDefinition::ARGNAME_DIRECTIVE_EXPRESSIONS] = $expressions;
                }
                if ($deprecationDescription = $schemaDefinitionResolver->getSchemaDirectiveDeprecationDescription($typeResolver)) {
                    $schemaDefinition[SchemaDefinition::ARGNAME_DEPRECATED] = \true;
                    $schemaDefinition[SchemaDefinition::ARGNAME_DEPRECATIONDESCRIPTION] = $deprecationDescription;
                }
                if ($args = $schemaDefinitionResolver->getSchemaDirectiveArgs($typeResolver)) {
                    $schemaDefinition[SchemaDefinition::ARGNAME_ARGS] = $this->getFilteredSchemaDirectiveArgs($typeResolver, $args);
                }
            }
            /**
             * Please notice: the version always comes from the directiveResolver, and not from the schemaDefinitionResolver
             * That is because it is the implementer the one who knows what version it is, and not the one defining the interface
             * If the interface changes, the implementer will need to change, so the version will be upgraded
             * But it could also be that the contract doesn't change, but the implementation changes
             * it's really not their responsibility
             */
            if (Environment::enableSemanticVersionConstraints()) {
                if ($version = $this->getSchemaDirectiveVersion($typeResolver)) {
                    $schemaDefinition[SchemaDefinition::ARGNAME_VERSION] = $version;
                }
            }
            $this->addSchemaDefinitionForDirective($schemaDefinition);
            $this->schemaDefinitionForDirectiveCache[$key] = $schemaDefinition;
        }
        return $this->schemaDefinitionForDirectiveCache[$key];
    }
    /**
     * Function to override
     */
    protected function addSchemaDefinitionForDirective(array &$schemaDefinition)
    {
    }
}
