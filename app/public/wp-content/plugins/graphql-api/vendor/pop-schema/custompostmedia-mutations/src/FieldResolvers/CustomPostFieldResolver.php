<?php

declare (strict_types=1);
namespace PoPSchema\CustomPostMediaMutations\FieldResolvers;

use PoP\ComponentModel\FieldResolvers\AbstractDBDataFieldResolver;
use PoP\ComponentModel\HelperServices\SemverHelperServiceInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\SchemaTypeModifiers;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\Engine\CMS\CMSServiceInterface;
use PoP\Hooks\HooksAPIInterface;
use PoP\LooseContracts\NameResolverInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\CustomPostMediaMutations\MutationResolvers\MutationInputProperties;
use PoPSchema\CustomPostMediaMutations\MutationResolvers\RemoveFeaturedImageOnCustomPostMutationResolver;
use PoPSchema\CustomPostMediaMutations\MutationResolvers\SetFeaturedImageOnCustomPostMutationResolver;
use PoPSchema\CustomPosts\FieldInterfaceResolvers\IsCustomPostFieldInterfaceResolver;
use PoPSchema\CustomPosts\TypeResolvers\CustomPostUnionTypeResolver;
use PoPSchema\Media\TypeResolvers\MediaTypeResolver;
class CustomPostFieldResolver extends AbstractDBDataFieldResolver
{
    /**
     * @var \PoPSchema\Media\TypeResolvers\MediaTypeResolver
     */
    protected $mediaTypeResolver;
    public function __construct(TranslationAPIInterface $translationAPI, HooksAPIInterface $hooksAPI, InstanceManagerInterface $instanceManager, FieldQueryInterpreterInterface $fieldQueryInterpreter, NameResolverInterface $nameResolver, CMSServiceInterface $cmsService, SemverHelperServiceInterface $semverHelperService, MediaTypeResolver $mediaTypeResolver)
    {
        $this->mediaTypeResolver = $mediaTypeResolver;
        parent::__construct($translationAPI, $hooksAPI, $instanceManager, $fieldQueryInterpreter, $nameResolver, $cmsService, $semverHelperService);
    }
    public function getClassesToAttachTo() : array
    {
        return array(IsCustomPostFieldInterfaceResolver::class);
    }
    public function getFieldNamesToResolve() : array
    {
        return ['setFeaturedImage', 'removeFeaturedImage'];
    }
    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        $descriptions = ['setFeaturedImage' => $this->translationAPI->__('Set the featured image on the custom post', 'custompostmedia-mutations'), 'removeFeaturedImage' => $this->translationAPI->__('Remove the featured image on the custom post', 'custompostmedia-mutations')];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }
    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName) : string
    {
        $types = ['setFeaturedImage' => SchemaDefinition::TYPE_ID, 'removeFeaturedImage' => SchemaDefinition::TYPE_ID];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }
    public function getSchemaFieldTypeModifiers(TypeResolverInterface $typeResolver, string $fieldName) : ?int
    {
        $nonNullableFieldNames = ['setFeaturedImage', 'removeFeaturedImage'];
        if (\in_array($fieldName, $nonNullableFieldNames)) {
            return SchemaTypeModifiers::NON_NULLABLE;
        }
        return parent::getSchemaFieldTypeModifiers($typeResolver, $fieldName);
    }
    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName) : array
    {
        switch ($fieldName) {
            case 'setFeaturedImage':
                return [[SchemaDefinition::ARGNAME_NAME => MutationInputProperties::MEDIA_ITEM_ID, SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_ID, SchemaDefinition::ARGNAME_DESCRIPTION => \sprintf($this->translationAPI->__('The ID of the featured image, of type \'%s\'', 'custompostmedia-mutations'), $this->mediaTypeResolver->getTypeName()), SchemaDefinition::ARGNAME_MANDATORY => \true]];
        }
        return parent::getSchemaFieldArgs($typeResolver, $fieldName);
    }
    /**
     * Validated the mutation on the resultItem because the ID
     * is obtained from the same object, so it's not originally
     * present in $form_data
     */
    public function validateMutationOnResultItem(TypeResolverInterface $typeResolver, string $fieldName) : bool
    {
        switch ($fieldName) {
            case 'setFeaturedImage':
            case 'removeFeaturedImage':
                return \true;
        }
        return parent::validateMutationOnResultItem($typeResolver, $fieldName);
    }
    /**
     * @param object $resultItem
     */
    protected function getFieldArgsToExecuteMutation(array $fieldArgs, TypeResolverInterface $typeResolver, $resultItem, string $fieldName) : array
    {
        $fieldArgs = parent::getFieldArgsToExecuteMutation($fieldArgs, $typeResolver, $resultItem, $fieldName);
        $customPost = $resultItem;
        switch ($fieldName) {
            case 'setFeaturedImage':
            case 'removeFeaturedImage':
                $fieldArgs[MutationInputProperties::CUSTOMPOST_ID] = $typeResolver->getID($customPost);
                break;
        }
        return $fieldArgs;
    }
    public function resolveFieldMutationResolverClass(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        switch ($fieldName) {
            case 'setFeaturedImage':
                return SetFeaturedImageOnCustomPostMutationResolver::class;
            case 'removeFeaturedImage':
                return RemoveFeaturedImageOnCustomPostMutationResolver::class;
        }
        return parent::resolveFieldMutationResolverClass($typeResolver, $fieldName);
    }
    public function resolveFieldTypeResolverClass(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        switch ($fieldName) {
            case 'setFeaturedImage':
            case 'removeFeaturedImage':
                return CustomPostUnionTypeResolver::class;
        }
        return parent::resolveFieldTypeResolverClass($typeResolver, $fieldName);
    }
}
