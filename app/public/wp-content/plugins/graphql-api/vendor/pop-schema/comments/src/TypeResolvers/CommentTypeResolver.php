<?php

declare (strict_types=1);
namespace PoPSchema\Comments\TypeResolvers;

use PoP\ComponentModel\ErrorHandling\ErrorProviderInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Schema\FeedbackMessageStoreInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\Schema\SchemaDefinitionServiceInterface;
use PoP\ComponentModel\TypeResolvers\AbstractTypeResolver;
use PoP\Hooks\HooksAPIInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface;
use PoPSchema\Comments\TypeDataLoaders\CommentTypeDataLoader;
class CommentTypeResolver extends AbstractTypeResolver
{
    /**
     * @var \PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface
     */
    protected $commentTypeAPI;
    public function __construct(TranslationAPIInterface $translationAPI, HooksAPIInterface $hooksAPI, InstanceManagerInterface $instanceManager, FeedbackMessageStoreInterface $feedbackMessageStore, FieldQueryInterpreterInterface $fieldQueryInterpreter, ErrorProviderInterface $errorProvider, SchemaDefinitionServiceInterface $schemaDefinitionService, CommentTypeAPIInterface $commentTypeAPI)
    {
        $this->commentTypeAPI = $commentTypeAPI;
        parent::__construct($translationAPI, $hooksAPI, $instanceManager, $feedbackMessageStore, $fieldQueryInterpreter, $errorProvider, $schemaDefinitionService);
    }
    public function getTypeName() : string
    {
        return 'Comment';
    }
    public function getSchemaTypeDescription() : ?string
    {
        return $this->translationAPI->__('Comments added to posts', 'comments');
    }
    /**
     * @return string|int|null
     * @param object $resultItem
     */
    public function getID($resultItem)
    {
        $comment = $resultItem;
        return $this->commentTypeAPI->getCommentId($comment);
    }
    public function getTypeDataLoaderClass() : string
    {
        return CommentTypeDataLoader::class;
    }
}
