<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\ModuleResolvers;

use GraphQLAPI\GraphQLAPI\ModuleResolvers\AbstractModuleResolver;
use GraphQLAPI\GraphQLAPI\Constants\ModuleSettingOptions;
use GraphQLAPI\GraphQLAPI\ModuleResolvers\SchemaTypeModuleResolver as GraphQLAPISchemaTypeModuleResolver;
use GraphQLAPI\GraphQLAPI\ModuleResolvers\SchemaTypeModuleResolverTrait;
use GraphQLAPI\GraphQLAPI\ModuleSettings\Properties;
use GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface;
use GraphQLAPI\GraphQLAPI\ModuleResolvers\ModuleResolverTrait;
use GraphQLAPI\GraphQLAPI\Plugin;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\Comments\TypeResolvers\CommentTypeResolver;
use PoPSchema\PostCategories\TypeResolvers\PostCategoryTypeResolver;
use PoPSchema\Posts\TypeResolvers\PostTypeResolver;
use PoPSchema\PostTags\TypeResolvers\PostTagTypeResolver;
use PoPSchema\SchemaCommons\Constants\Behaviors;
use PoPSchema\Users\TypeResolvers\UserTypeResolver;

class MetaSchemaTypeModuleResolver extends AbstractModuleResolver
{
    use ModuleResolverTrait;
    use SchemaTypeModuleResolverTrait {
        getPriority as getUpstreamPriority;
    }

    public const SCHEMA_CUSTOMPOST_META = Plugin::NAMESPACE . '\schema-custompost-meta';
    public const SCHEMA_USER_META = Plugin::NAMESPACE . '\schema-user-meta';
    public const SCHEMA_COMMENT_META = Plugin::NAMESPACE . '\schema-comment-meta';
    public const SCHEMA_TAXONOMY_META = Plugin::NAMESPACE . '\schema-taxonomy-meta';
    /**
     * @var \PoPSchema\Comments\TypeResolvers\CommentTypeResolver|null
     */
    protected $commentTypeResolver;
    /**
     * @var \PoPSchema\PostTags\TypeResolvers\PostTagTypeResolver|null
     */
    protected $postTagTypeResolver;
    /**
     * @var \PoPSchema\PostCategories\TypeResolvers\PostCategoryTypeResolver|null
     */
    protected $postCategoryTypeResolver;
    /**
     * @var \PoPSchema\Posts\TypeResolvers\PostTypeResolver|null
     */
    protected $postTypeResolver;
    /**
     * @var \PoPSchema\Users\TypeResolvers\UserTypeResolver|null
     */
    protected $userTypeResolver;

    /**
     * Make all properties nullable, becase the ModuleRegistry is registered
     * in the SystemContainer, where there are no typeResolvers so it will be null,
     * and in the ApplicationContainer, from where the "Modules" page is resolved
     * and which does have all the typeResolvers.
     * Function `getDescription` will only be accessed from the Application Container,
     * so the properties will not be null in that situation.
     */
    public function __construct(
        InstanceManagerInterface $instanceManager,
        ModuleRegistryInterface $moduleRegistry,
        TranslationAPIInterface $translationAPI,
        ?CommentTypeResolver $commentTypeResolver,
        ?PostTagTypeResolver $postTagTypeResolver,
        ?PostCategoryTypeResolver $postCategoryTypeResolver,
        ?PostTypeResolver $postTypeResolver,
        ?UserTypeResolver $userTypeResolver
    ) {
        $this->commentTypeResolver = $commentTypeResolver;
        $this->postTagTypeResolver = $postTagTypeResolver;
        $this->postCategoryTypeResolver = $postCategoryTypeResolver;
        $this->postTypeResolver = $postTypeResolver;
        $this->userTypeResolver = $userTypeResolver;
        parent::__construct($instanceManager, $moduleRegistry, $translationAPI);
    }

    /**
     * @return string[]
     */
    public function getModulesToResolve(): array
    {
        return [
            self::SCHEMA_CUSTOMPOST_META,
            self::SCHEMA_USER_META,
            self::SCHEMA_COMMENT_META,
            self::SCHEMA_TAXONOMY_META,
        ];
    }

    public function getPriority(): int
    {
        return $this->getUpstreamPriority() - 2;
    }

    /**
     * @return array<array> List of entries that must be satisfied, each entry is an array where at least 1 module must be satisfied
     */
    public function getDependedModuleLists(string $module): array
    {
        switch ($module) {
            case self::SCHEMA_CUSTOMPOST_META:
                return [
                    [
                        GraphQLAPISchemaTypeModuleResolver::SCHEMA_CUSTOMPOSTS,
                    ],
                ];
            case self::SCHEMA_USER_META:
                return [
                    [
                        GraphQLAPISchemaTypeModuleResolver::SCHEMA_USERS,
                    ],
                ];
            case self::SCHEMA_COMMENT_META:
                return [
                    [
                        GraphQLAPISchemaTypeModuleResolver::SCHEMA_COMMENTS,
                    ],
                ];
            case self::SCHEMA_TAXONOMY_META:
                return [
                    [
                        GraphQLAPISchemaTypeModuleResolver::SCHEMA_TAGS,
                        GraphQLAPISchemaTypeModuleResolver::SCHEMA_CATEGORIES,
                    ],
                ];
        }
        return parent::getDependedModuleLists($module);
    }

    public function getName(string $module): string
    {
        switch ($module) {
            case self::SCHEMA_CUSTOMPOST_META:
                return \__('Schema Custom Post Meta', 'graphql-api');
            case self::SCHEMA_USER_META:
                return \__('Schema User Meta', 'graphql-api');
            case self::SCHEMA_COMMENT_META:
                return \__('Schema Comment Meta', 'graphql-api');
            case self::SCHEMA_TAXONOMY_META:
                return \__('Schema Taxonomy Meta', 'graphql-api');
            default:
                return $module;
        }
    }

    public function getDescription(string $module): string
    {
        /**
         * Inner properties will not be null. Assign them their type,
         * to avoid PHPStan errors
         */
        /** @var CommentTypeResolver */
        $commentTypeResolver = $this->commentTypeResolver;
        /** @var PostTagTypeResolver */
        $postTagTypeResolver = $this->postTagTypeResolver;
        /** @var PostCategoryTypeResolver */
        $postCategoryTypeResolver = $this->postCategoryTypeResolver;
        /** @var PostTypeResolver */
        $postTypeResolver = $this->postTypeResolver;
        /** @var UserTypeResolver */
        $userTypeResolver = $this->userTypeResolver;
        switch ($module) {
            case self::SCHEMA_CUSTOMPOST_META:
                return sprintf(
                    \__('Add the <code>%1$s</code> field to custom posts, such as type <code>%2$s</code>', 'graphql-api'),
                    'metaValue',
                    $postTypeResolver->getTypeName()
                );
            case self::SCHEMA_USER_META:
                return sprintf(
                    \__('Add the <code>%1$s</code> field to type <code>%2$s</code>', 'graphql-api'),
                    'metaValue',
                    $userTypeResolver->getTypeName()
                );
            case self::SCHEMA_COMMENT_META:
                return sprintf(
                    \__('Add the <code>%1$s</code> field to type <code>%2$s</code>', 'graphql-api'),
                    'metaValue',
                    $commentTypeResolver->getTypeName()
                );
            case self::SCHEMA_TAXONOMY_META:
                return sprintf(
                    \__('Add the <code>%1$s</code> field to taxonomies, such as types <code>%2$s</code> and <code>%3$s</code>', 'graphql-api'),
                    'metaValue',
                    $postTagTypeResolver->getTypeName(),
                    $postCategoryTypeResolver->getTypeName()
                );
        }
        return parent::getDescription($module);
    }

    /**
     * Default value for an option set by the module
     * @return mixed
     */
    public function getSettingsDefaultValue(string $module, string $option)
    {
        $defaultMetaValues = [
            ModuleSettingOptions::ENTRIES => [],
            ModuleSettingOptions::BEHAVIOR => Behaviors::ALLOWLIST,
        ];
        $defaultValues = [
            self::SCHEMA_CUSTOMPOST_META => $defaultMetaValues,
            self::SCHEMA_USER_META => $defaultMetaValues,
            self::SCHEMA_COMMENT_META => $defaultMetaValues,
            self::SCHEMA_TAXONOMY_META => $defaultMetaValues,
        ];
        return $defaultValues[$module][$option] ?? null;
    }

    /**
     * Array with the inputs to show as settings for the module
     *
     * @return array<array> List of settings for the module, each entry is an array with property => value
     */
    public function getSettings(string $module): array
    {
        $moduleSettings = parent::getSettings($module);

        if (
            in_array($module, [
                self::SCHEMA_CUSTOMPOST_META,
                self::SCHEMA_USER_META,
                self::SCHEMA_COMMENT_META,
                self::SCHEMA_TAXONOMY_META,
            ])
        ) {
            $entriesTitle = \__('Meta keys', 'graphql-api');
            $metaKeyDesc = \__('List of all the meta keys, to either allow or deny access to, when querying field <code>meta</code> on %s.', 'graphql-api');
            $headsUpDesc = sprintf(\__('<strong>Heads up:</strong> Entries surrounded with <code>/</code> are evaluated as regex (regular expressions).', 'graphql-api'), 'option');
            $entryDesc = \__('Eg: Both entries <code>%1$s</code> and <code>/%2$s.*/</code> match option name <code>"%1$s"</code>.', 'graphql-api');
            $moduleDescriptions = [
                self::SCHEMA_CUSTOMPOST_META => sprintf(
                    \__('%1$s<hr/>%2$s<br/>%3$s', 'graphql-api'),
                    sprintf(
                        $metaKeyDesc,
                        'custom posts'
                    ),
                    $headsUpDesc,
                    sprintf(
                        $entryDesc,
                        '_edit_last',
                        '_edit_'
                    )
                ),
                self::SCHEMA_USER_META => sprintf(
                    \__('%1$s<hr/>%2$s<br/>%3$s', 'graphql-api'),
                    sprintf(
                        $metaKeyDesc,
                        'users'
                    ),
                    $headsUpDesc,
                    sprintf(
                        $entryDesc,
                        'last_name',
                        'last_'
                    )
                ),
                self::SCHEMA_COMMENT_META => sprintf(
                    \__('%1$s<hr/>%2$s<br/>%3$s', 'graphql-api'),
                    sprintf(
                        $metaKeyDesc,
                        'comments'
                    ),
                    $headsUpDesc,
                    sprintf(
                        $entryDesc,
                        'description',
                        'desc'
                    )
                ),
                self::SCHEMA_TAXONOMY_META => sprintf(
                    \__('%1$s<hr/>%2$s<br/>%3$s', 'graphql-api'),
                    sprintf(
                        $metaKeyDesc,
                        'taxonomies (tags and categories)'
                    ),
                    $headsUpDesc,
                    sprintf(
                        $entryDesc,
                        'description',
                        'desc'
                    )
                ),
            ];
            $option = ModuleSettingOptions::ENTRIES;
            $moduleSettings[] = [
                Properties::INPUT => $option,
                Properties::NAME => $this->getSettingOptionName(
                    $module,
                    $option
                ),
                Properties::TITLE => $entriesTitle,
                Properties::DESCRIPTION => $moduleDescriptions[$module],
                Properties::TYPE => Properties::TYPE_ARRAY,
            ];

            $option = ModuleSettingOptions::BEHAVIOR;
            $moduleSettings[] = [
                Properties::INPUT => $option,
                Properties::NAME => $this->getSettingOptionName(
                    $module,
                    $option
                ),
                Properties::TITLE => \__('Behavior', 'graphql-api'),
                Properties::DESCRIPTION => \__('Are the entries being allowed or denied?<ul><li>???????? Allow access: only the configured entries can be accessed, and no other can.</li><li>???????? Deny access: the configured entries cannot be accessed, all other entries can.</li></ul>', 'graphql-api'),
                Properties::TYPE => Properties::TYPE_STRING,
                Properties::POSSIBLE_VALUES => [
                    Behaviors::ALLOWLIST => \__('Allow access', 'graphql-api'),
                    Behaviors::DENYLIST => \__('Deny access', 'graphql-api'),
                ],
            ];
        }

        return $moduleSettings;
    }
}
