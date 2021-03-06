<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\SchemaConfiguratorExecuters;

use GraphQLAPI\GraphQLAPI\Services\CustomPostTypes\GraphQLCustomEndpointCustomPostType;
use GraphQLAPI\GraphQLAPI\Services\SchemaConfigurators\EndpointSchemaConfigurator;
use GraphQLAPI\GraphQLAPI\Services\SchemaConfigurators\SchemaConfiguratorInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;

class EndpointSchemaConfiguratorExecuter extends AbstractLoadingCPTSchemaConfiguratorExecuter
{
    /**
     * @var \GraphQLAPI\GraphQLAPI\Services\SchemaConfigurators\EndpointSchemaConfigurator
     */
    protected $endpointSchemaConfigurator;
    public function __construct(
        InstanceManagerInterface $instanceManager,
        EndpointSchemaConfigurator $endpointSchemaConfigurator
    ) {
        $this->endpointSchemaConfigurator = $endpointSchemaConfigurator;
        parent::__construct($instanceManager);
    }

    protected function getCustomPostType(): string
    {
        /** @var GraphQLCustomEndpointCustomPostType */
        $customPostTypeService = $this->instanceManager->getInstance(GraphQLCustomEndpointCustomPostType::class);
        return $customPostTypeService->getCustomPostType();
    }

    protected function getSchemaConfigurator(): SchemaConfiguratorInterface
    {
        return $this->endpointSchemaConfigurator;
    }
}
