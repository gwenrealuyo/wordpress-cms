<?php

declare (strict_types=1);
namespace PoPSchema\CommentMutations\FieldResolvers;

use PoP\ComponentModel\FieldResolvers\AbstractDBDataFieldResolver;
use PoP\ComponentModel\HelperServices\SemverHelperServiceInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\Engine\CMS\CMSServiceInterface;
use PoP\Hooks\HooksAPIInterface;
use PoP\LooseContracts\NameResolverInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\CommentMutations\MutationResolvers\AddCommentToCustomPostMutationResolver;
use PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties;
use PoPSchema\CommentMutations\Schema\SchemaDefinitionHelpers;
use PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface;
use PoPSchema\Comments\TypeResolvers\CommentTypeResolver;
class CommentFieldResolver extends AbstractDBDataFieldResolver
{
    /**
     * @var \PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface
     */
    protected $commentTypeAPI;
    public function __construct(TranslationAPIInterface $translationAPI, HooksAPIInterface $hooksAPI, InstanceManagerInterface $instanceManager, FieldQueryInterpreterInterface $fieldQueryInterpreter, NameResolverInterface $nameResolver, CMSServiceInterface $cmsService, SemverHelperServiceInterface $semverHelperService, CommentTypeAPIInterface $commentTypeAPI)
    {
        $this->commentTypeAPI = $commentTypeAPI;
        parent::__construct($translationAPI, $hooksAPI, $instanceManager, $fieldQueryInterpreter, $nameResolver, $cmsService, $semverHelperService);
    }
    public function getClassesToAttachTo() : array
    {
        return array(CommentTypeResolver::class);
    }
    public function getFieldNamesToResolve() : array
    {
        return ['reply'];
    }
    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        $descriptions = ['reply' => $this->translationAPI->__('Reply a comment with another comment', 'comment-mutations')];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }
    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName) : string
    {
        $types = ['reply' => SchemaDefinition::TYPE_ID];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }
    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName) : array
    {
        switch ($fieldName) {
            case 'reply':
                return SchemaDefinitionHelpers::getAddCommentToCustomPostSchemaFieldArgs($typeResolver, $fieldName, \false, \false);
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
            case 'reply':
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
        $comment = $resultItem;
        switch ($fieldName) {
            case 'reply':
                $fieldArgs[MutationInputProperties::CUSTOMPOST_ID] = $this->commentTypeAPI->getCommentPostId($comment);
                $fieldArgs[MutationInputProperties::PARENT_COMMENT_ID] = $typeResolver->getID($comment);
                break;
        }
        return $fieldArgs;
    }
    public function resolveFieldMutationResolverClass(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        switch ($fieldName) {
            case 'reply':
                return AddCommentToCustomPostMutationResolver::class;
        }
        return parent::resolveFieldMutationResolverClass($typeResolver, $fieldName);
    }
    public function resolveFieldTypeResolverClass(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        switch ($fieldName) {
            case 'reply':
                return CommentTypeResolver::class;
        }
        return parent::resolveFieldTypeResolverClass($typeResolver, $fieldName);
    }
}
