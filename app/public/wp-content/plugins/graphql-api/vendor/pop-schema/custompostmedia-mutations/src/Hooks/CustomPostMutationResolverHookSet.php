<?php

declare (strict_types=1);
namespace PoPSchema\CustomPostMediaMutations\Hooks;

use PoP\Hooks\AbstractHookSet;
use PoP\Hooks\HooksAPIInterface;
use PoP\Translation\TranslationAPIInterface;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoPSchema\Media\TypeResolvers\MediaTypeResolver;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoPSchema\CustomPostMutations\Schema\SchemaDefinitionHelpers;
use PoPSchema\CustomPostMediaMutations\MutationResolvers\MutationInputProperties;
use PoPSchema\CustomPostMediaMutations\Facades\CustomPostMediaTypeMutationAPIFacade;
use PoPSchema\CustomPostMutations\MutationResolvers\AbstractCreateUpdateCustomPostMutationResolver;
class CustomPostMutationResolverHookSet extends AbstractHookSet
{
    /**
     * @var \PoPSchema\Media\TypeResolvers\MediaTypeResolver
     */
    protected $mediaTypeResolver;
    public function __construct(HooksAPIInterface $hooksAPI, TranslationAPIInterface $translationAPI, InstanceManagerInterface $instanceManager, MediaTypeResolver $mediaTypeResolver)
    {
        $this->mediaTypeResolver = $mediaTypeResolver;
        parent::__construct($hooksAPI, $translationAPI, $instanceManager);
    }
    protected function init() : void
    {
        $this->hooksAPI->addFilter(SchemaDefinitionHelpers::HOOK_UPDATE_SCHEMA_FIELD_ARGS, array($this, 'getSchemaFieldArgs'), 10, 3);
        $this->hooksAPI->addAction(AbstractCreateUpdateCustomPostMutationResolver::HOOK_EXECUTE_CREATE_OR_UPDATE, array($this, 'setOrRemoveFeaturedImage'), 10, 2);
    }
    public function getSchemaFieldArgs(array $fieldArgs, TypeResolverInterface $typeResolver, string $fieldName) : array
    {
        $fieldArgs[] = [SchemaDefinition::ARGNAME_NAME => MutationInputProperties::FEATUREDIMAGE_ID, SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_ID, SchemaDefinition::ARGNAME_DESCRIPTION => \sprintf($this->translationAPI->__('The ID of the featured image (of type %s)', 'custompost-mutations'), $this->mediaTypeResolver->getTypeName())];
        return $fieldArgs;
    }
    /**
     * If entry "featuredImageID" has an ID, set it. If it is null, remove it
     * @param int|string $customPostID
     */
    public function setOrRemoveFeaturedImage($customPostID, array $form_data) : void
    {
        $customPostMediaTypeMutationAPI = CustomPostMediaTypeMutationAPIFacade::getInstance();
        if (isset($form_data[MutationInputProperties::FEATUREDIMAGE_ID])) {
            if ($featuredImageID = $form_data[MutationInputProperties::FEATUREDIMAGE_ID]) {
                $customPostMediaTypeMutationAPI->setFeaturedImage($customPostID, $featuredImageID);
            } else {
                $customPostMediaTypeMutationAPI->removeFeaturedImage($customPostID);
            }
        }
    }
}
