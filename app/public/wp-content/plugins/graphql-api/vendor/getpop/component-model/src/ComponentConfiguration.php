<?php

declare (strict_types=1);
namespace PoP\ComponentModel;

use PoP\ComponentModel\Constants\Params;
use PoP\ComponentModel\Tokens\Param;
use PoP\ComponentModel\ComponentConfiguration\EnvironmentValueHelpers;
use PoP\ComponentModel\ComponentConfiguration\ComponentConfigurationTrait;
use PoP\Root\Environment as RootEnvironment;
class ComponentConfiguration
{
    use ComponentConfigurationTrait;
    /**
     * Map with the configuration passed by params
     *
     * @var array
     */
    private static $overrideConfiguration = [];
    /**
     * @var bool
     */
    private static $enableConfigByParams = \false;
    /**
     * @var bool
     */
    private static $useComponentModelCache = \false;
    /**
     * @var bool
     */
    private static $enableSchemaEntityRegistries = \false;
    /**
     * @var bool
     */
    private static $namespaceTypesAndInterfaces = \false;
    /**
     * @var bool
     */
    private static $useSingleTypeInsteadOfUnionType = \false;
    /**
     * @var bool
     */
    private static $enableAdminSchema = \false;
    /**
     * @var bool
     */
    private static $validateFieldTypeResponseWithSchemaDefinition = \false;
    /**
     * @var bool
     */
    private static $treatTypeCoercingFailuresAsErrors = \false;
    /**
     * @var bool
     */
    private static $treatUndefinedFieldOrDirectiveArgsAsErrors = \false;
    /**
     * @var bool
     */
    private static $setFailingFieldResponseAsNull = \false;
    /**
     * @var bool
     */
    private static $removeFieldIfDirectiveFailed = \false;
    /**
     * @var bool
     */
    private static $coerceInputFromSingleValueToList = \false;
    /**
     * Initialize component configuration
     */
    public static function init() : void
    {
        // Allow to override the configuration with values passed in the query string:
        // "config": comma-separated string with all fields with value "true"
        // Whatever fields are not there, will be considered "false"
        self::$overrideConfiguration = array();
        if (self::enableConfigByParams()) {
            self::$overrideConfiguration = isset($_REQUEST[Params::CONFIG]) ? \explode(Param::VALUE_SEPARATOR, $_REQUEST[Params::CONFIG]) : array();
        }
    }
    /**
     * Indicate if the configuration is overriden by params
     */
    public static function doingOverrideConfiguration() : bool
    {
        return !empty(self::$overrideConfiguration);
    }
    /**
     * Obtain the override configuration for a key, with possible values being only
     * `true` or `false`, or `null` if that key is not set
     *
     * @param string $key the key to get the value
     */
    public static function getOverrideConfiguration(string $key) : ?bool
    {
        // If no values where defined in the configuration, then skip it completely
        if (empty(self::$overrideConfiguration)) {
            return null;
        }
        // Check if the key has been given value "true"
        if (\in_array($key, self::$overrideConfiguration)) {
            return \true;
        }
        // Otherwise, it has value "false"
        return \false;
    }
    /**
     * Access layer to the environment variable, enabling to override its value
     * Indicate if the configuration can be set through params
     */
    public static function enableConfigByParams() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::ENABLE_CONFIG_BY_PARAMS;
        $selfProperty =& self::$enableConfigByParams;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    /**
     * Access layer to the environment variable, enabling to override its value
     * Indicate if to use the cache
     */
    public static function useComponentModelCache() : bool
    {
        // If we are overriding the configuration, then do NOT use the cache
        // Otherwise, parameters from the config have need to be added to $vars, however they can't,
        // since we want the $vars model_instance_id to not change when testing with the "config" param
        if (self::doingOverrideConfiguration()) {
            return \false;
        }
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::USE_COMPONENT_MODEL_CACHE;
        $selfProperty =& self::$useComponentModelCache;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    /**
     * Access layer to the environment variable, enabling to override its value
     * Indicate if to keep the several entities that make up a schema (types, directives) in a registry
     * This functionality is not used by PoP itself, hence it defaults to `false`
     * It can be used by making a mapping from type name to type resolver class, as to reference a type
     * by a name, if needed (eg: to save in the application's configuration)
     */
    public static function enableSchemaEntityRegistries() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::ENABLE_SCHEMA_ENTITY_REGISTRIES;
        $selfProperty =& self::$enableSchemaEntityRegistries;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    public static function namespaceTypesAndInterfaces() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::NAMESPACE_TYPES_AND_INTERFACES;
        $selfProperty =& self::$namespaceTypesAndInterfaces;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    public static function useSingleTypeInsteadOfUnionType() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::USE_SINGLE_TYPE_INSTEAD_OF_UNION_TYPE;
        $selfProperty =& self::$useSingleTypeInsteadOfUnionType;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    public static function enableAdminSchema() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::ENABLE_ADMIN_SCHEMA;
        $selfProperty =& self::$enableAdminSchema;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    /**
     * By default, validate for DEV only
     */
    public static function validateFieldTypeResponseWithSchemaDefinition() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::VALIDATE_FIELD_TYPE_RESPONSE_WITH_SCHEMA_DEFINITION;
        $selfProperty =& self::$validateFieldTypeResponseWithSchemaDefinition;
        $defaultValue = RootEnvironment::isApplicationEnvironmentDev();
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    /**
     * By default, errors produced from casting a type (eg: "3.5 to int")
     * are treated as warnings, not errors
     */
    public static function treatTypeCoercingFailuresAsErrors() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::TREAT_TYPE_COERCING_FAILURES_AS_ERRORS;
        $selfProperty =& self::$treatTypeCoercingFailuresAsErrors;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    /**
     * By default, querying for a field or directive argument
     * which has not been defined in the schema
     * is treated as a warning, not an error
     */
    public static function treatUndefinedFieldOrDirectiveArgsAsErrors() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::TREAT_UNDEFINED_FIELD_OR_DIRECTIVE_ARGS_AS_ERRORS;
        $selfProperty =& self::$treatUndefinedFieldOrDirectiveArgsAsErrors;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    /**
     * The GraphQL spec indicates that, when a field produces an error (during
     * value resolution or coercion) then its response must be set as null:
     *
     *   If a field error is raised while resolving a field, it is handled as though the field returned null, and the error must be added to the "errors" list in the response.
     *
     * @see https://spec.graphql.org/draft/#sec-Handling-Field-Errors
     */
    public static function setFailingFieldResponseAsNull() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::SET_FAILING_FIELD_RESPONSE_AS_NULL;
        $selfProperty =& self::$setFailingFieldResponseAsNull;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    /**
     * Indicate: If a directive fails, then remove the affected IDs/fields from the upcoming stages of the directive pipeline execution
     */
    public static function removeFieldIfDirectiveFailed() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::REMOVE_FIELD_IF_DIRECTIVE_FAILED;
        $selfProperty =& self::$removeFieldIfDirectiveFailed;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    /**
     * Support passing a single value where a list is expected.
     * Defined in the GraphQL spec.
     *
     * @see https://spec.graphql.org/draft/#sec-List.Input-Coercion
     */
    public static function coerceInputFromSingleValueToList() : bool
    {
        // Define properties
        $envVariable = \PoP\ComponentModel\Environment::COERCE_INPUT_FROM_SINGLE_VALUE_TO_LIST;
        $selfProperty =& self::$coerceInputFromSingleValueToList;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
}
