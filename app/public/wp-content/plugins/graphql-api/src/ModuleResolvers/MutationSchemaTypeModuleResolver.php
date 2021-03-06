<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\ModuleResolvers;

use GraphQLAPI\GraphQLAPI\ModuleResolvers\AbstractModuleResolver;
use GraphQLAPI\GraphQLAPI\ModuleResolvers\ModuleResolverTrait;
use GraphQLAPI\GraphQLAPI\Plugin;

class MutationSchemaTypeModuleResolver extends AbstractModuleResolver
{
    use ModuleResolverTrait {
        ModuleResolverTrait::hasDocumentation as upstreamHasDocumentation;
    }
    use SchemaTypeModuleResolverTrait {
        getPriority as getUpstreamPriority;
    }

    public const SCHEMA_MUTATIONS = Plugin::NAMESPACE . '\schema-mutations';
    public const SCHEMA_USER_STATE_MUTATIONS = Plugin::NAMESPACE . '\schema-user-state-mutations';
    public const SCHEMA_CUSTOMPOST_MUTATIONS = Plugin::NAMESPACE . '\schema-custompost-mutations';
    public const SCHEMA_POST_MUTATIONS = Plugin::NAMESPACE . '\schema-post-mutations';
    public const SCHEMA_CUSTOMPOSTMEDIA_MUTATIONS = Plugin::NAMESPACE . '\schema-custompostmedia-mutations';
    public const SCHEMA_POST_TAG_MUTATIONS = Plugin::NAMESPACE . '\schema-post-tag-mutations';
    public const SCHEMA_POST_CATEGORY_MUTATIONS = Plugin::NAMESPACE . '\schema-post-category-mutations';
    public const SCHEMA_COMMENT_MUTATIONS = Plugin::NAMESPACE . '\schema-comment-mutations';

    /**
     * @return string[]
     */
    public function getModulesToResolve(): array
    {
        return [
            self::SCHEMA_MUTATIONS,
            self::SCHEMA_USER_STATE_MUTATIONS,
            self::SCHEMA_CUSTOMPOST_MUTATIONS,
            self::SCHEMA_POST_MUTATIONS,
            self::SCHEMA_CUSTOMPOSTMEDIA_MUTATIONS,
            self::SCHEMA_POST_TAG_MUTATIONS,
            self::SCHEMA_POST_CATEGORY_MUTATIONS,
            self::SCHEMA_COMMENT_MUTATIONS,
        ];
    }

    public function getPriority(): int
    {
        return $this->getUpstreamPriority() - 5;
    }

    /**
     * @return array<array> List of entries that must be satisfied, each entry is an array where at least 1 module must be satisfied
     */
    public function getDependedModuleLists(string $module): array
    {
        switch ($module) {
            case self::SCHEMA_USER_STATE_MUTATIONS:
                return [
                    [
                        self::SCHEMA_MUTATIONS,
                    ],
                ];
            case self::SCHEMA_CUSTOMPOST_MUTATIONS:
                return [
                    [
                        self::SCHEMA_USER_STATE_MUTATIONS,
                    ],
                    [
                        SchemaTypeModuleResolver::SCHEMA_CUSTOMPOSTS,
                    ],
                ];
            case self::SCHEMA_POST_MUTATIONS:
                return [
                    [
                        SchemaTypeModuleResolver::SCHEMA_POSTS,
                    ],
                    [
                        self::SCHEMA_CUSTOMPOST_MUTATIONS,
                    ],
                ];
            case self::SCHEMA_CUSTOMPOSTMEDIA_MUTATIONS:
                return [
                    [
                        SchemaTypeModuleResolver::SCHEMA_MEDIA,
                    ],
                    [
                        self::SCHEMA_CUSTOMPOST_MUTATIONS,
                    ],
                ];
            case self::SCHEMA_POST_TAG_MUTATIONS:
                return [
                    [
                        SchemaTypeModuleResolver::SCHEMA_POST_TAGS,
                    ],
                    [
                        self::SCHEMA_POST_MUTATIONS,
                    ],
                ];
            case self::SCHEMA_POST_CATEGORY_MUTATIONS:
                return [
                    [
                        SchemaTypeModuleResolver::SCHEMA_POST_CATEGORIES,
                    ],
                    [
                        self::SCHEMA_POST_MUTATIONS,
                    ],
                ];
            case self::SCHEMA_COMMENT_MUTATIONS:
                return [
                    [
                        self::SCHEMA_USER_STATE_MUTATIONS,
                    ],
                    [
                        SchemaTypeModuleResolver::SCHEMA_COMMENTS,
                    ],
                ];
        }
        return parent::getDependedModuleLists($module);
    }

    public function getName(string $module): string
    {
        switch ($module) {
            case self::SCHEMA_MUTATIONS:
                return \__('Schema Mutations', 'graphql-api');
            case self::SCHEMA_USER_STATE_MUTATIONS:
                return \__('Schema User State Mutations', 'graphql-api');
            case self::SCHEMA_CUSTOMPOST_MUTATIONS:
                return \__('Schema Custom Post Mutations', 'graphql-api');
            case self::SCHEMA_POST_MUTATIONS:
                return \__('Schema Post Mutations', 'graphql-api');
            case self::SCHEMA_CUSTOMPOSTMEDIA_MUTATIONS:
                return \__('Schema Custom Post Media Mutations', 'graphql-api');
            case self::SCHEMA_POST_TAG_MUTATIONS:
                return \__('Schema Post Tag Mutations', 'graphql-api');
            case self::SCHEMA_POST_CATEGORY_MUTATIONS:
                return \__('Schema Post Category Mutations', 'graphql-api');
            case self::SCHEMA_COMMENT_MUTATIONS:
                return \__('Schema Comment Mutations', 'graphql-api');
            default:
                return $module;
        }
    }

    public function getDescription(string $module): string
    {
        switch ($module) {
            case self::SCHEMA_MUTATIONS:
                return \__('Modify data by executing mutations', 'graphql-api');
            case self::SCHEMA_USER_STATE_MUTATIONS:
                return \__('Have the user log-in, and be able to perform mutations', 'graphql-api');
            case self::SCHEMA_CUSTOMPOST_MUTATIONS:
                return \__('Base functionality to mutate custom posts', 'graphql-api');
            case self::SCHEMA_POST_MUTATIONS:
                return sprintf(
                    \__('Execute mutations on %1$s', 'graphql-api'),
                    \__('posts', 'graphql-api')
                );
            case self::SCHEMA_CUSTOMPOSTMEDIA_MUTATIONS:
                return \__('Execute mutations concerning media items on custom posts', 'graphql-api');
            case self::SCHEMA_POST_TAG_MUTATIONS:
                return \__('Add tags to posts', 'graphql-api');
            case self::SCHEMA_POST_CATEGORY_MUTATIONS:
                return \__('Add categories to posts', 'graphql-api');
            case self::SCHEMA_COMMENT_MUTATIONS:
                return \__('Create comments', 'graphql-api');
        }
        return parent::getDescription($module);
    }

    /**
     * Does the module have HTML Documentation?
     */
    public function hasDocumentation(string $module): bool
    {
        switch ($module) {
            case self::SCHEMA_CUSTOMPOST_MUTATIONS:
            case self::SCHEMA_POST_MUTATIONS:
            case self::SCHEMA_CUSTOMPOSTMEDIA_MUTATIONS:
            case self::SCHEMA_POST_TAG_MUTATIONS:
            case self::SCHEMA_POST_CATEGORY_MUTATIONS:
            case self::SCHEMA_COMMENT_MUTATIONS:
                return false;
        }
        return $this->upstreamHasDocumentation($module);
    }
}
