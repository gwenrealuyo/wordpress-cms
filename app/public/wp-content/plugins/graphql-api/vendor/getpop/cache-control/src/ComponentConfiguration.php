<?php

declare (strict_types=1);
namespace PoP\CacheControl;

use PoP\ComponentModel\ComponentConfiguration\EnvironmentValueHelpers;
use PoP\ComponentModel\ComponentConfiguration\ComponentConfigurationTrait;
class ComponentConfiguration
{
    use ComponentConfigurationTrait;
    /**
     * @var int
     */
    private static $getDefaultCacheControlMaxAge = 3600;
    public static function getDefaultCacheControlMaxAge() : int
    {
        // Define properties
        $envVariable = \PoP\CacheControl\Environment::DEFAULT_CACHE_CONTROL_MAX_AGE;
        $selfProperty =& self::$getDefaultCacheControlMaxAge;
        $defaultValue = 3600;
        // 1 hour
        $callback = [EnvironmentValueHelpers::class, 'toInt'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
}
