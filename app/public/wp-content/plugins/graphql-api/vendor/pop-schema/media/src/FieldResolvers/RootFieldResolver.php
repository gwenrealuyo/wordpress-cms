<?php

declare (strict_types=1);
namespace PoPSchema\Media\FieldResolvers;

use PoP\ComponentModel\FieldResolvers\AbstractQueryableFieldResolver;
use PoP\ComponentModel\HelperServices\SemverHelperServiceInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\SchemaTypeModifiers;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\Engine\CMS\CMSServiceInterface;
use PoP\Engine\TypeResolvers\RootTypeResolver;
use PoP\Hooks\HooksAPIInterface;
use PoP\LooseContracts\NameResolverInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\CustomPosts\TypeResolvers\CustomPostTypeResolver;
use PoPSchema\Media\TypeAPIs\MediaTypeAPIInterface;
use PoPSchema\Media\TypeResolvers\MediaTypeResolver;
use PoPSchema\SchemaCommons\DataLoading\ReturnTypes;
class RootFieldResolver extends AbstractQueryableFieldResolver
{
    /**
     * @var \PoPSchema\CustomPosts\TypeResolvers\CustomPostTypeResolver
     */
    protected $customPostTypeResolver;
    /**
     * @var \PoPSchema\Media\TypeAPIs\MediaTypeAPIInterface
     */
    protected $mediaTypeAPI;
    public function __construct(TranslationAPIInterface $translationAPI, HooksAPIInterface $hooksAPI, InstanceManagerInterface $instanceManager, FieldQueryInterpreterInterface $fieldQueryInterpreter, NameResolverInterface $nameResolver, CMSServiceInterface $cmsService, SemverHelperServiceInterface $semverHelperService, CustomPostTypeResolver $customPostTypeResolver, MediaTypeAPIInterface $mediaTypeAPI)
    {
        $this->customPostTypeResolver = $customPostTypeResolver;
        $this->mediaTypeAPI = $mediaTypeAPI;
        parent::__construct($translationAPI, $hooksAPI, $instanceManager, $fieldQueryInterpreter, $nameResolver, $cmsService, $semverHelperService);
    }
    public function getClassesToAttachTo() : array
    {
        return array(RootTypeResolver::class);
    }
    public function getFieldNamesToResolve() : array
    {
        return ['mediaItems', 'mediaItem'];
    }
    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        $descriptions = ['mediaItems' => $this->translationAPI->__('Get the media items', 'media'), 'mediaItem' => $this->translationAPI->__('Get a media item', 'media')];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }
    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName) : string
    {
        $types = ['mediaItems' => SchemaDefinition::TYPE_ID, 'mediaItem' => SchemaDefinition::TYPE_ID];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }
    public function getSchemaFieldTypeModifiers(TypeResolverInterface $typeResolver, string $fieldName) : ?int
    {
        switch ($fieldName) {
            case 'mediaItems':
                return SchemaTypeModifiers::NON_NULLABLE | SchemaTypeModifiers::IS_ARRAY;
            default:
                return parent::getSchemaFieldTypeModifiers($typeResolver, $fieldName);
        }
    }
    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName) : array
    {
        switch ($fieldName) {
            case 'mediaItem':
                return [[SchemaDefinition::ARGNAME_NAME => 'id', SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_ID, SchemaDefinition::ARGNAME_DESCRIPTION => \sprintf($this->translationAPI->__('The ID of the media element, of type \'%s\'', 'media'), $this->customPostTypeResolver->getTypeName()), SchemaDefinition::ARGNAME_MANDATORY => \true]];
        }
        return parent::getSchemaFieldArgs($typeResolver, $fieldName);
    }
    /**
     * @param array<string, mixed> $fieldArgs
     * @param array<string, mixed>|null $variables
     * @param array<string, mixed>|null $expressions
     * @param array<string, mixed> $options
     * @return mixed
     * @param object $resultItem
     */
    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
    {
        switch ($fieldName) {
            case 'mediaItems':
            case 'mediaItem':
                $query = [];
                if ($fieldName == 'mediaItem') {
                    $query['include'] = [$fieldArgs['id']];
                }
                $options = ['return-type' => ReturnTypes::IDS];
                $mediaItems = $this->mediaTypeAPI->getMediaElements($query, $options);
                if ($fieldName == 'mediaItem') {
                    return \count($mediaItems) > 0 ? $mediaItems[0] : null;
                }
                return $mediaItems;
        }
        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
    public function resolveFieldTypeResolverClass(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        switch ($fieldName) {
            case 'mediaItems':
            case 'mediaItem':
                return MediaTypeResolver::class;
        }
        return parent::resolveFieldTypeResolverClass($typeResolver, $fieldName);
    }
}
