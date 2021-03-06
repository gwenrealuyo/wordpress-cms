<?php

declare (strict_types=1);
namespace PoP\Engine\DirectiveResolvers;

use PoP\ComponentModel\Container\ServiceTags\MandatoryDirectiveServiceTagInterface;
use PoP\ComponentModel\DirectiveResolvers\AbstractGlobalDirectiveResolver;
use PoP\ComponentModel\Directives\DirectiveTypes;
use PoP\ComponentModel\TypeResolvers\PipelinePositions;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\Engine\Dataloading\Expressions;
use PoP\FieldQuery\QueryHelpers;
final class SetSelfAsExpressionDirectiveResolver extends AbstractGlobalDirectiveResolver implements MandatoryDirectiveServiceTagInterface
{
    public function getDirectiveName() : string
    {
        return 'setSelfAsExpression';
    }
    /**
     * This is a "Scripting" type directive
     */
    public function getDirectiveType() : string
    {
        return DirectiveTypes::SCRIPTING;
    }
    /**
     * Do not allow dynamic fields
     */
    protected function disableDynamicFieldsFromDirectiveArgs() : bool
    {
        return \true;
    }
    /**
     * This directive must go at the very beginning
     */
    public function getPipelinePosition() : string
    {
        return PipelinePositions::BEFORE_VALIDATE;
    }
    /**
     * Setting it more than once makes no sense
     */
    public function isRepeatable() : bool
    {
        return \false;
    }
    public function getSchemaDirectiveDescription(TypeResolverInterface $typeResolver) : ?string
    {
        return \sprintf($this->translationAPI->__('Place the current object\'s data under expression `%s`, making it accessible to fields and directives through helper function `getPropertyFromSelf`', 'component-model'), QueryHelpers::getExpressionQuery(Expressions::NAME_SELF));
    }
    public function getSchemaDirectiveExpressions(TypeResolverInterface $typeResolver) : array
    {
        return [Expressions::NAME_SELF => $this->translationAPI->__('Object containing all properties for the current object, fetched either in the current or a previous iteration. These properties can be accessed through helper function `getSelfProp`', 'component-model')];
    }
    /**
     * Copy the data under the relational object into the current object
     */
    public function resolveDirective(TypeResolverInterface $typeResolver, array &$idsDataFields, array &$succeedingPipelineIDsDataFields, array &$succeedingPipelineDirectiveResolverInstances, array &$resultIDItems, array &$unionDBKeyIDs, array &$dbItems, array &$previousDBItems, array &$variables, array &$messages, array &$dbErrors, array &$dbWarnings, array &$dbDeprecations, array &$dbNotices, array &$dbTraces, array &$schemaErrors, array &$schemaWarnings, array &$schemaDeprecations, array &$schemaNotices, array &$schemaTraces) : void
    {
        // The name of the variable is always set to "self", accessed as $self
        $dbKey = $typeResolver->getTypeOutputName();
        foreach (\array_keys($idsDataFields) as $id) {
            // Make an array of references, pointing to the position of the current object in arrays $dbItems and $previousDBItems;
            // It is extremeley important to make it by reference, so that when the 2 variables are updated later on during the current iteration,
            // the new values are immediately available to all fields and directives executed later during the same iteration
            $value = ['dbItems' => &$dbItems[(string) $id], 'previousDBItems' => &$previousDBItems[$dbKey][(string) $id]];
            $this->addExpressionForResultItem($id, Expressions::NAME_SELF, $value, $messages);
        }
    }
}
