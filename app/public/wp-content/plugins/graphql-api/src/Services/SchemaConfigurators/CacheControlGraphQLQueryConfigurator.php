<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\SchemaConfigurators;

use PoP\ComponentModel\Misc\GeneralUtils;
use GraphQLAPI\GraphQLAPI\Services\Helpers\BlockHelpers;
use GraphQLAPI\GraphQLAPI\Services\Blocks\CacheControlBlock;
use GraphQLAPI\GraphQLAPI\Services\Blocks\AbstractControlBlock;
use PoP\CacheControl\Facades\CacheControlManagerFacade;
use GraphQLAPI\GraphQLAPI\Services\SchemaConfigurators\AbstractGraphQLQueryConfigurator;
use GraphQLAPI\GraphQLAPI\ModuleResolvers\PerformanceFunctionalityModuleResolver;

class CacheControlGraphQLQueryConfigurator extends AbstractGraphQLQueryConfigurator
{
    public function isServiceEnabled(): bool
    {
        // Only execute for GET operations
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            return false;
        }

        return parent::isServiceEnabled();
    }

    public function getEnablingModule(): ?string
    {
        return PerformanceFunctionalityModuleResolver::CACHE_CONTROL;
    }

    /**
     * Extract the configuration items defined in the CPT,
     * and inject them into the service as to take effect in the current GraphQL query
     */
    protected function doExecuteSchemaConfiguration(int $cclPostID): void
    {
        /** @var BlockHelpers */
        $blockHelpers = $this->instanceManager->getInstance(BlockHelpers::class);
        /**
         * @var CacheControlBlock
         */
        $block = $this->instanceManager->getInstance(CacheControlBlock::class);
        $cclBlockItems = $blockHelpers->getBlocksOfTypeFromCustomPost(
            $cclPostID,
            $block
        );
        $cacheControlManager = CacheControlManagerFacade::getInstance();
        // The "Cache Control" type contains the fields/directives and the max-age
        foreach ($cclBlockItems as $cclBlockItem) {
            $maxAge = $cclBlockItem['attrs'][CacheControlBlock::ATTRIBUTE_NAME_CACHE_CONTROL_MAX_AGE] ?? null;
            if (!is_null($maxAge) && $maxAge >= 0) {
                // Extract the saved fields
                if ($typeFields = $cclBlockItem['attrs'][AbstractControlBlock::ATTRIBUTE_NAME_TYPE_FIELDS] ?? null) {
                    if (
                        $entriesForFields = GeneralUtils::arrayFlatten(
                            array_map(
                                function ($selectedField) use ($maxAge) {
                                    return $this->getEntriesFromField($selectedField, $maxAge);
                                },
                                $typeFields
                            )
                        )
                    ) {
                        $cacheControlManager->addEntriesForFields(
                            $entriesForFields
                        );
                    }
                }

                // Extract the saved directives
                if ($directives = $cclBlockItem['attrs'][AbstractControlBlock::ATTRIBUTE_NAME_DIRECTIVES] ?? null) {
                    if (
                        $entriesForDirectives = GeneralUtils::arrayFlatten(array_filter(
                            array_map(
                                function ($selectedDirective) use ($maxAge) {
                                    return $this->getEntriesFromDirective($selectedDirective, $maxAge);
                                },
                                $directives
                            )
                        ))
                    ) {
                        $cacheControlManager->addEntriesForDirectives(
                            $entriesForDirectives
                        );
                    }
                }
            }
        }
    }
}
