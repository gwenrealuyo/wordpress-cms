<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\SchemaConfigurators;

use GraphQLAPI\GraphQLAPI\Registries\EndpointSchemaConfigurationExecuterRegistryInterface;
use GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface;
use GraphQLAPI\GraphQLAPI\Registries\SchemaConfigurationExecuterRegistryInterface;
use GraphQLAPI\GraphQLAPI\Services\SchemaConfigurators\AbstractEndpointSchemaConfigurator;
use PoP\ComponentModel\Instances\InstanceManagerInterface;

class EndpointSchemaConfigurator extends AbstractEndpointSchemaConfigurator
{
    /**
     * @var \GraphQLAPI\GraphQLAPI\Registries\EndpointSchemaConfigurationExecuterRegistryInterface
     */
    protected $endpointSchemaConfigurationExecuterRegistry;
    public function __construct(
        InstanceManagerInterface $instanceManager,
        ModuleRegistryInterface $moduleRegistry,
        EndpointSchemaConfigurationExecuterRegistryInterface $endpointSchemaConfigurationExecuterRegistry
    ) {
        $this->endpointSchemaConfigurationExecuterRegistry = $endpointSchemaConfigurationExecuterRegistry;
        parent::__construct($instanceManager, $moduleRegistry);
    }

    protected function getSchemaConfigurationExecuterRegistry(): SchemaConfigurationExecuterRegistryInterface
    {
        return $this->endpointSchemaConfigurationExecuterRegistry;
    }
}
