<?php

declare (strict_types=1);
namespace PoPSchema\Settings;

use PoP\ComponentModel\ComponentConfiguration\EnvironmentValueHelpers;
use PoP\ComponentModel\ComponentConfiguration\ComponentConfigurationTrait;
use PoPSchema\SchemaCommons\Constants\Behaviors;
class ComponentConfiguration
{
    use ComponentConfigurationTrait;
    /**
     * @var mixed[]
     */
    private static $getSettingsEntries = [];
    /**
     * @var string
     */
    private static $getSettingsBehavior = Behaviors::ALLOWLIST;
    public static function getSettingsEntries() : array
    {
        // Define properties
        $envVariable = \PoPSchema\Settings\Environment::SETTINGS_ENTRIES;
        $selfProperty =& self::$getSettingsEntries;
        $defaultValue = [];
        $callback = [EnvironmentValueHelpers::class, 'commaSeparatedStringToArray'];
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue, $callback);
        return $selfProperty;
    }
    public static function getSettingsBehavior() : string
    {
        // Define properties
        $envVariable = \PoPSchema\Settings\Environment::SETTINGS_BEHAVIOR;
        $selfProperty =& self::$getSettingsBehavior;
        $defaultValue = Behaviors::ALLOWLIST;
        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue($envVariable, $selfProperty, $defaultValue);
        return $selfProperty;
    }
}
