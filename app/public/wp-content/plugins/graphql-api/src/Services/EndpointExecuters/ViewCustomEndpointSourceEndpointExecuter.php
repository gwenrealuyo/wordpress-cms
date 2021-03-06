<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\EndpointExecuters;

use GraphQLAPI\GraphQLAPI\ModuleResolvers\EndpointFunctionalityModuleResolver;
use GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface;
use GraphQLAPI\GraphQLAPI\Services\CustomPostTypes\GraphQLCustomEndpointCustomPostType;
use GraphQLAPI\GraphQLAPI\Services\CustomPostTypes\GraphQLEndpointCustomPostTypeInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;

class ViewCustomEndpointSourceEndpointExecuter extends AbstractViewSourceEndpointExecuter implements CustomEndpointExecuterServiceTagInterface
{
    /**
     * @var \GraphQLAPI\GraphQLAPI\Services\CustomPostTypes\GraphQLCustomEndpointCustomPostType
     */
    protected $graphQLCustomEndpointCustomPostType;
    public function __construct(InstanceManagerInterface $instanceManager, ModuleRegistryInterface $moduleRegistry, GraphQLCustomEndpointCustomPostType $graphQLCustomEndpointCustomPostType)
    {
        $this->graphQLCustomEndpointCustomPostType = $graphQLCustomEndpointCustomPostType;
        parent::__construct($instanceManager, $moduleRegistry);
    }
    public function getEnablingModule(): ?string
    {
        return EndpointFunctionalityModuleResolver::CUSTOM_ENDPOINTS;
    }

    protected function getCustomPostType(): GraphQLEndpointCustomPostTypeInterface
    {
        return $this->graphQLCustomEndpointCustomPostType;
    }
}
