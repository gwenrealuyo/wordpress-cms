<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\EndpointAnnotators;

use GraphQLAPI\GraphQLAPI\Constants\RequestParams;
use GraphQLAPI\GraphQLAPI\ModuleResolvers\ClientFunctionalityModuleResolver;
use GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface;
use GraphQLAPI\GraphQLAPI\Services\Blocks\AbstractBlock;
use GraphQLAPI\GraphQLAPI\Services\Blocks\EndpointGraphiQLBlock;
use GraphQLAPI\GraphQLAPI\Services\CustomPostTypes\GraphQLCustomEndpointCustomPostType;
use GraphQLAPI\GraphQLAPI\Services\Helpers\BlockHelpers;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use WP_Post;

class GraphiQLClientEndpointAnnotator extends AbstractClientEndpointAnnotator implements CustomEndpointAnnotatorServiceTagInterface
{
    /**
     * @var \GraphQLAPI\GraphQLAPI\Services\Blocks\EndpointGraphiQLBlock
     */
    protected $endpointGraphiQLBlock;
    public function __construct(InstanceManagerInterface $instanceManager, ModuleRegistryInterface $moduleRegistry, BlockHelpers $blockHelpers, GraphQLCustomEndpointCustomPostType $graphQLCustomEndpointCustomPostType, EndpointGraphiQLBlock $endpointGraphiQLBlock)
    {
        $this->endpointGraphiQLBlock = $endpointGraphiQLBlock;
        parent::__construct(
            $instanceManager,
            $moduleRegistry,
            $blockHelpers,
            $graphQLCustomEndpointCustomPostType
        );
    }
    public function getEnablingModule(): ?string
    {
        return ClientFunctionalityModuleResolver::GRAPHIQL_FOR_CUSTOM_ENDPOINTS;
    }

    /**
     * Add actions to the CPT list
     * @param array<string, string> $actions
     */
    public function addCustomPostTypeTableActions(array &$actions, WP_Post $post): void
    {
        // Check the client has not been disabled in the CPT
        if (!$this->isClientEnabled($post)) {
            return;
        }

        if ($permalink = \get_permalink($post->ID)) {
            $title = \_draft_or_post_title();
            $actions['graphiql'] = sprintf(
                '<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
                \add_query_arg(
                    RequestParams::VIEW,
                    RequestParams::VIEW_GRAPHIQL,
                    $permalink
                ),
                /* translators: %s: Post title. */
                \esc_attr(\sprintf(\__('GraphiQL &#8220;%s&#8221;'), $title)),
                __('GraphiQL', 'graphql-api')
            );
        }
    }

    protected function getBlock(): AbstractBlock
    {
        return $this->endpointGraphiQLBlock;
    }
}
