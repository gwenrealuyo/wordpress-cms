<?php

declare (strict_types=1);
namespace PoPSchema\CustomPosts;

use PoP\ComponentModel\ComponentConfiguration\EnvironmentValueHelpers;
use PoP\ComponentModel\ComponentConfiguration\ComponentConfigurationTrait;
class ComponentConfiguration
{
    use ComponentConfigurationTrait;
    /**
     * @var int|null
     */
    private static $getCustomPostListDefaultLimit;
    /**
     * @var int|null
     */
    private static $getCustomPostListMaxLimit;
    /**
     * @var bool
     */
    private static $useSingleTypeInsteadOfCustomPostUnionType = \false;
    public static function getCustomPostListDefaultLimit() : ?int
    {
        // Define properties
        $envVariable = \PoPSchema\CustomPosts\Environment::CUSTOMPOST_LIST_DEFAULT_LIMIT;
        $selfProperty =& self::$getCustomPostListDefaultLimit;
        $defaultValue = 10;
        $callback = [EnvironmentValueHelpers::class, 'toInt'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    public static function getCustomPostListMaxLimit() : ?int
    {
        // Define properties
        $envVariable = \PoPSchema\CustomPosts\Environment::CUSTOMPOST_LIST_MAX_LIMIT;
        $selfProperty =& self::$getCustomPostListMaxLimit;
        $defaultValue = -1;
        // Unlimited
        $callback = [EnvironmentValueHelpers::class, 'toInt'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    public static function useSingleTypeInsteadOfCustomPostUnionType() : bool
    {
        // Define properties
        $envVariable = \PoPSchema\CustomPosts\Environment::USE_SINGLE_TYPE_INSTEAD_OF_CUSTOMPOST_UNION_TYPE;
        $selfProperty =& self::$useSingleTypeInsteadOfCustomPostUnionType;
        $defaultValue = \false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
}
