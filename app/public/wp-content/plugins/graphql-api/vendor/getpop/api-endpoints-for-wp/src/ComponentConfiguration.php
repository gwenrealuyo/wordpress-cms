<?php

declare(strict_types=1);

namespace PoP\APIEndpointsForWP;

use PoP\APIEndpoints\EndpointUtils;
use PoP\ComponentModel\ComponentConfiguration\EnvironmentValueHelpers;
use PoP\ComponentModel\ComponentConfiguration\ComponentConfigurationTrait;

class ComponentConfiguration
{
    use ComponentConfigurationTrait;

    /**
     * @var bool
     */
    private static $isNativeAPIEndpointDisabled = false;
    /**
     * @var string
     */
    private static $getNativeAPIEndpoint = '/api/';

    public static function isNativeAPIEndpointDisabled(): bool
    {
        // Define properties
        $envVariable = Environment::DISABLE_NATIVE_API_ENDPOINT;
        $selfProperty = &self::$isNativeAPIEndpointDisabled;
        $defaultValue = false;
        $callback = [EnvironmentValueHelpers::class, 'toBool'];

        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue(
            $envVariable,
            $selfProperty,
            $defaultValue,
            $callback
        );
        return $selfProperty;
    }

    public static function getNativeAPIEndpoint(): string
    {
        // Define properties
        $envVariable = Environment::NATIVE_API_ENDPOINT;
        $selfProperty = &self::$getNativeAPIEndpoint;
        $defaultValue = '/api/';
        $callback = [EndpointUtils::class, 'slashURI'];

        // Initialize property from the environment/hook
        self::maybeInitializeConfigurationValue(
            $envVariable,
            $selfProperty,
            $defaultValue,
            $callback
        );
        return $selfProperty;
    }
}
