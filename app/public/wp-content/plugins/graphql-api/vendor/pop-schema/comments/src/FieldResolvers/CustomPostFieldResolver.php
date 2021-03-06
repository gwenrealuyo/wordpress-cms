<?php

declare (strict_types=1);
namespace PoPSchema\Comments\FieldResolvers;

use PoP\ComponentModel\FieldResolvers\AbstractQueryableFieldResolver;
use PoP\ComponentModel\FieldResolvers\FieldSchemaDefinitionResolverInterface;
use PoP\ComponentModel\HelperServices\SemverHelperServiceInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\Engine\CMS\CMSServiceInterface;
use PoP\Hooks\HooksAPIInterface;
use PoP\LooseContracts\NameResolverInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\Comments\Constants\Status;
use PoPSchema\Comments\FieldInterfaceResolvers\CommentableFieldInterfaceResolver;
use PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface;
use PoPSchema\Comments\TypeResolvers\CommentTypeResolver;
use PoPSchema\CustomPosts\FieldInterfaceResolvers\IsCustomPostFieldInterfaceResolver;
use PoPSchema\SchemaCommons\DataLoading\ReturnTypes;
class CustomPostFieldResolver extends AbstractQueryableFieldResolver
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
        return [IsCustomPostFieldInterfaceResolver::class];
    }
    public function getImplementedFieldInterfaceResolverClasses() : array
    {
        return [CommentableFieldInterfaceResolver::class];
    }
    public function getFieldNamesToResolve() : array
    {
        return ['areCommentsOpen', 'commentCount', 'hasComments', 'comments'];
    }
    /**
     * By returning `null`, the schema definition comes from the interface
     */
    public function getSchemaDefinitionResolver(TypeResolverInterface $typeResolver) : ?FieldSchemaDefinitionResolverInterface
    {
        return null;
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
        $post = $resultItem;
        switch ($fieldName) {
            case 'areCommentsOpen':
                return $this->commentTypeAPI->areCommentsOpen($typeResolver->getID($post));
            case 'commentCount':
                return $this->commentTypeAPI->getCommentNumber($typeResolver->getID($post));
            case 'hasComments':
                return $typeResolver->resolveValue($post, 'commentCount', $variables, $expressions, $options) > 0;
            case 'comments':
                $query = array(
                    'status' => Status::APPROVED,
                    // 'type' => 'comment', // Only comments, no trackbacks or pingbacks
                    'customPostID' => $typeResolver->getID($post),
                    // The Order must always be date > ASC so the jQuery works in inserting sub-comments in already-created parent comments
                    'order' => 'ASC',
                    'orderby' => $this->nameResolver->getName('popcms:dbcolumn:orderby:comments:date'),
                    'parentID' => 0,
                );
                $options = ['return-type' => ReturnTypes::IDS];
                $this->addFilterDataloadQueryArgs($options, $typeResolver, $fieldName, $fieldArgs);
                return $this->commentTypeAPI->getComments($query, $options);
        }
        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
    public function resolveFieldTypeResolverClass(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        switch ($fieldName) {
            case 'comments':
                return CommentTypeResolver::class;
        }
        return parent::resolveFieldTypeResolverClass($typeResolver, $fieldName);
    }
}
