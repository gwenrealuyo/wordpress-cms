<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\BlockAccessors;

use GraphQLAPI\GraphQLAPI\GetterSetterObjects\BlockAttributes\PersistedQueryEndpointAPIHierarchyBlockAttributes;
use GraphQLAPI\GraphQLAPI\Services\Blocks\PersistedQueryEndpointAPIHierarchyBlock;
use GraphQLAPI\GraphQLAPI\Services\Helpers\BlockHelpers;
use WP_Post;

class PersistedQueryEndpointAPIHierarchyBlockAccessor
{
    /**
     * @var \GraphQLAPI\GraphQLAPI\Services\Helpers\BlockHelpers
     */
    protected $blockHelpers;
    /**
     * @var \GraphQLAPI\GraphQLAPI\Services\Blocks\PersistedQueryEndpointAPIHierarchyBlock
     */
    protected $persistedQueryEndpointAPIHierarchyBlock;
    public function __construct(BlockHelpers $blockHelpers, PersistedQueryEndpointAPIHierarchyBlock $persistedQueryEndpointAPIHierarchyBlock)
    {
        $this->blockHelpers = $blockHelpers;
        $this->persistedQueryEndpointAPIHierarchyBlock = $persistedQueryEndpointAPIHierarchyBlock;
    }
    /**
     * Extract the Persisted Query Options block attributes from the post
     */
    public function getAttributes(WP_Post $post): ?PersistedQueryEndpointAPIHierarchyBlockAttributes
    {
        $apiHierarchyBlock = $this->blockHelpers->getSingleBlockOfTypeFromCustomPost(
            $post,
            $this->persistedQueryEndpointAPIHierarchyBlock
        );
        // If there is either 0 or more than 1, return nothing
        if ($apiHierarchyBlock === null) {
            return null;
        }
        return new PersistedQueryEndpointAPIHierarchyBlockAttributes($apiHierarchyBlock['attrs'][PersistedQueryEndpointAPIHierarchyBlock::ATTRIBUTE_NAME_INHERIT_QUERY] ?? false);
    }
}
