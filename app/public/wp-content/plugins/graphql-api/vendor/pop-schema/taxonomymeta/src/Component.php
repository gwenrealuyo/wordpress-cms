<?php

declare (strict_types=1);
namespace PoPSchema\TaxonomyMeta;

use PoP\Root\Component\AbstractComponent;
/**
 * Initialize component
 */
class Component extends AbstractComponent
{
    /**
     * Classes from PoP components that must be initialized before this component
     *
     * @return string[]
     */
    public static function getDependedComponentClasses() : array
    {
        return [\PoPSchema\Meta\Component::class, \PoPSchema\Taxonomies\Component::class];
    }
    /**
     * Initialize services
     *
     * @param array<string, mixed> $configuration
     * @param string[] $skipSchemaComponentClasses
     */
    protected static function initializeContainerServices(array $configuration = [], bool $skipSchema = \false, array $skipSchemaComponentClasses = []) : void
    {
        \PoPSchema\TaxonomyMeta\ComponentConfiguration::setConfiguration($configuration);
        self::initSchemaServices(\dirname(__DIR__), $skipSchema);
    }
}
