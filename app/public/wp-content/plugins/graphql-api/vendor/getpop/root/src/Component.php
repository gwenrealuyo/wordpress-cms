<?php

declare (strict_types=1);
namespace PoP\Root;

use PoP\Root\Component\ApplicationEvents;
use PoP\Root\Component\AbstractComponent;
use PoP\Root\Container\HybridCompilerPasses\AutomaticallyInstantiatedServiceCompilerPass;
use PoP\Root\Container\ContainerBuilderFactory;
use PoP\Root\Container\SystemContainerBuilderFactory;
use PoP\Root\Container\ServiceInstantiatorInterface;
use PoP\Root\Container\SystemCompilerPasses\RegisterSystemCompilerPassServiceCompilerPass;
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
        return [];
    }
    /**
     * Compiler Passes for the System Container
     *
     * @return string[]
     */
    public static function getSystemContainerCompilerPassClasses() : array
    {
        return [
            RegisterSystemCompilerPassServiceCompilerPass::class,
            // Needed to initialize ModuleListTableAction
            AutomaticallyInstantiatedServiceCompilerPass::class,
        ];
    }
    /**
     * Initialize services for the system container
     */
    protected static function initializeSystemContainerServices() : void
    {
        self::initSystemServices(\dirname(__DIR__), '', 'hybrid-services.yaml');
        self::initSystemServices(\dirname(__DIR__));
    }
    /**
     * Initialize services
     *
     * @param array<string, mixed> $configuration
     * @param string[] $skipSchemaComponentClasses
     */
    protected static function initializeContainerServices(array $configuration = [], bool $skipSchema = \false, array $skipSchemaComponentClasses = []) : void
    {
        self::initServices(\dirname(__DIR__), '', 'hybrid-services.yaml');
    }
    /**
     * Function called by the Bootloader after initializing the SystemContainer
     */
    public static function bootSystem() : void
    {
        // Initialize container services through AutomaticallyInstantiatedServiceCompilerPass
        /**
         * @var ServiceInstantiatorInterface
         */
        $serviceInstantiator = SystemContainerBuilderFactory::getInstance()->get(ServiceInstantiatorInterface::class);
        $serviceInstantiator->initializeServices();
    }
    /**
     * Function called by the Bootloader after all components have been loaded
     */
    public static function beforeBoot() : void
    {
        // Initialize container services through AutomaticallyInstantiatedServiceCompilerPass
        /**
         * @var ServiceInstantiatorInterface
         */
        $serviceInstantiator = ContainerBuilderFactory::getInstance()->get(ServiceInstantiatorInterface::class);
        $serviceInstantiator->initializeServices(ApplicationEvents::BEFORE_BOOT);
    }
    /**
     * Function called by the Bootloader after all components have been loaded
     */
    public static function boot() : void
    {
        // Initialize container services through AutomaticallyInstantiatedServiceCompilerPass
        /**
         * @var ServiceInstantiatorInterface
         */
        $serviceInstantiator = ContainerBuilderFactory::getInstance()->get(ServiceInstantiatorInterface::class);
        $serviceInstantiator->initializeServices(ApplicationEvents::BOOT);
    }
    /**
     * Function called by the Bootloader after all components have been loaded
     */
    public static function afterBoot() : void
    {
        // Initialize container services through AutomaticallyInstantiatedServiceCompilerPass
        /**
         * @var ServiceInstantiatorInterface
         */
        $serviceInstantiator = ContainerBuilderFactory::getInstance()->get(ServiceInstantiatorInterface::class);
        $serviceInstantiator->initializeServices(ApplicationEvents::AFTER_BOOT);
    }
}
