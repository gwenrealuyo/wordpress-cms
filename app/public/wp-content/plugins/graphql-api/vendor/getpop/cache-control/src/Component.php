<?php

declare (strict_types=1);
namespace PoP\CacheControl;

use PoP\Root\Component\AbstractComponent;
use PoP\Root\Component\CanDisableComponentTrait;
/**
 * Initialize component
 */
class Component extends AbstractComponent
{
    use CanDisableComponentTrait;
    /**
     * Classes from PoP components that must be initialized before this component
     *
     * @return string[]
     */
    public static function getDependedComponentClasses() : array
    {
        return [\PoP\MandatoryDirectivesByConfiguration\Component::class];
    }
    /**
     * Initialize services
     *
     * @param array<string, mixed> $configuration
     * @param string[] $skipSchemaComponentClasses
     */
    protected static function initializeContainerServices(array $configuration = [], bool $skipSchema = \false, array $skipSchemaComponentClasses = []) : void
    {
        if (self::isEnabled()) {
            self::initServices(\dirname(__DIR__));
            self::initSchemaServices(\dirname(__DIR__), $skipSchema);
        }
    }
    protected static function resolveEnabled() : bool
    {
        return !\PoP\CacheControl\Environment::disableCacheControl();
    }
}
