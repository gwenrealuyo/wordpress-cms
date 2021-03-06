<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\EndpointExecuters;

use GraphQLAPI\GraphQLAPI\Constants\RequestParams;
use GraphQLAPI\GraphQLAPI\ModuleResolvers\ClientFunctionalityModuleResolver;
use GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface;
use GraphQLAPI\GraphQLAPI\Services\Clients\CustomEndpointGraphiQLClient;
use GraphQLAPI\GraphQLAPI\Services\CustomPostTypes\GraphQLCustomEndpointCustomPostType;
use GraphQLAPI\GraphQLAPI\Services\EndpointAnnotators\ClientEndpointAnnotatorInterface;
use GraphQLAPI\GraphQLAPI\Services\EndpointAnnotators\GraphiQLClientEndpointAnnotator;
use GraphQLByPoP\GraphQLClientsForWP\Clients\AbstractClient;
use PoP\ComponentModel\Instances\InstanceManagerInterface;

class GraphiQLClientEndpointExecuter extends AbstractClientEndpointExecuter implements CustomEndpointExecuterServiceTagInterface
{
    /**
     * @var \GraphQLAPI\GraphQLAPI\Services\Clients\CustomEndpointGraphiQLClient
     */
    protected $customEndpointGraphiQLClient;
    /**
     * @var \GraphQLAPI\GraphQLAPI\Services\EndpointAnnotators\GraphiQLClientEndpointAnnotator
     */
    protected $graphiQLClientEndpointAnnotator;
    public function __construct(InstanceManagerInterface $instanceManager, ModuleRegistryInterface $moduleRegistry, GraphQLCustomEndpointCustomPostType $graphQLCustomEndpointCustomPostType, CustomEndpointGraphiQLClient $customEndpointGraphiQLClient, GraphiQLClientEndpointAnnotator $graphiQLClientEndpointAnnotator)
    {
        $this->customEndpointGraphiQLClient = $customEndpointGraphiQLClient;
        $this->graphiQLClientEndpointAnnotator = $graphiQLClientEndpointAnnotator;
        parent::__construct($instanceManager, $moduleRegistry, $graphQLCustomEndpointCustomPostType);
    }
    public function getEnablingModule(): ?string
    {
        return ClientFunctionalityModuleResolver::GRAPHIQL_FOR_CUSTOM_ENDPOINTS;
    }

    protected function getView(): string
    {
        return RequestParams::VIEW_GRAPHIQL;
    }

    protected function getClient(): AbstractClient
    {
        return $this->customEndpointGraphiQLClient;
    }

    protected function getClientEndpointAnnotator(): ClientEndpointAnnotatorInterface
    {
        return $this->graphiQLClientEndpointAnnotator;
    }
}
