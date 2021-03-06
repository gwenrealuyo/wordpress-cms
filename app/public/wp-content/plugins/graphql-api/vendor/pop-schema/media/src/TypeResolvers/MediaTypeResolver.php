<?php

declare (strict_types=1);
namespace PoPSchema\Media\TypeResolvers;

use PoP\ComponentModel\ErrorHandling\ErrorProviderInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Schema\FeedbackMessageStoreInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\Schema\SchemaDefinitionServiceInterface;
use PoP\ComponentModel\TypeResolvers\AbstractTypeResolver;
use PoP\Hooks\HooksAPIInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\Media\TypeAPIs\MediaTypeAPIInterface;
use PoPSchema\Media\TypeDataLoaders\MediaTypeDataLoader;
class MediaTypeResolver extends AbstractTypeResolver
{
    /**
     * @var \PoPSchema\Media\TypeAPIs\MediaTypeAPIInterface
     */
    protected $mediaTypeAPI;
    public function __construct(TranslationAPIInterface $translationAPI, HooksAPIInterface $hooksAPI, InstanceManagerInterface $instanceManager, FeedbackMessageStoreInterface $feedbackMessageStore, FieldQueryInterpreterInterface $fieldQueryInterpreter, ErrorProviderInterface $errorProvider, SchemaDefinitionServiceInterface $schemaDefinitionService, MediaTypeAPIInterface $mediaTypeAPI)
    {
        $this->mediaTypeAPI = $mediaTypeAPI;
        parent::__construct($translationAPI, $hooksAPI, $instanceManager, $feedbackMessageStore, $fieldQueryInterpreter, $errorProvider, $schemaDefinitionService);
    }
    public function getTypeName() : string
    {
        return 'Media';
    }
    public function getSchemaTypeDescription() : ?string
    {
        return $this->translationAPI->__('Media elements (such as images, videos, etc), attached to a post or independent', 'media');
    }
    /**
     * @return string|int|null
     * @param object $resultItem
     */
    public function getID($resultItem)
    {
        $media = $resultItem;
        return $this->mediaTypeAPI->getMediaElementId($media);
    }
    public function getTypeDataLoaderClass() : string
    {
        return MediaTypeDataLoader::class;
    }
}
