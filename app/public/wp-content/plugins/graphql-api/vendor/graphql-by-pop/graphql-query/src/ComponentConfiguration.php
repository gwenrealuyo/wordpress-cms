<?php

declare (strict_types=1);
namespace GraphQLByPoP\GraphQLQuery;

use PoP\ComponentModel\ComponentConfiguration\EnvironmentValueHelpers;
use PoP\ComponentModel\ComponentConfiguration\ComponentConfigurationTrait;
class ComponentConfiguration
{
    use ComponentConfigurationTrait;
    /**
     * @var bool
     */
    private static $enableVariablesAsExpressions = \false;
    /**
     * @var bool
     */
    private static $enableComposableDirectives = \false;
    public static function enableVariablesAsExpressions() : bool
    {
        // Define properties
        $envVariable = \GraphQLByPoP\GraphQLQuery\Environment::ENABLE_VARIABLES_AS_EXPRESSIONS;
        $selfProperty =& self::$enableVariablesAsExpressions;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    public static function enableComposableDirectives() : bool
    {
        // Define properties
        $envVariable = \GraphQLByPoP\GraphQLQuery\Environment::ENABLE_COMPOSABLE_DIRECTIVES;
        $selfProperty =& self::$enableComposableDirectives;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
}
