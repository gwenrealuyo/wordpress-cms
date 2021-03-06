<?php

declare (strict_types=1);
namespace PoPSchema\Comments\ConditionalOnComponent\Users\FieldResolvers;

use PoP\ComponentModel\HelperServices\SemverHelperServiceInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\Engine\CMS\CMSServiceInterface;
use PoP\Hooks\HooksAPIInterface;
use PoP\LooseContracts\NameResolverInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\Comments\ComponentConfiguration;
use PoPSchema\Comments\ConditionalOnComponent\Users\TypeAPIs\CommentTypeAPIInterface as UserCommentTypeAPIInterface;
use PoPSchema\Comments\FieldResolvers\CommentFieldResolver as UpstreamCommentFieldResolver;
use PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface;
use PoPSchema\Users\TypeAPIs\UserTypeAPIInterface;
/**
 * Override fields from the upstream class, getting the data from the user
 */
class CommentFieldResolver extends UpstreamCommentFieldResolver
{
    /**
     * @var UserCommentTypeAPIInterface
     */
    protected $userCommentTypeAPI;
    /**
     * @var \PoPSchema\Users\TypeAPIs\UserTypeAPIInterface
     */
    protected $userTypeAPI;
    public function __construct(TranslationAPIInterface $translationAPI, HooksAPIInterface $hooksAPI, InstanceManagerInterface $instanceManager, FieldQueryInterpreterInterface $fieldQueryInterpreter, NameResolverInterface $nameResolver, CMSServiceInterface $cmsService, SemverHelperServiceInterface $semverHelperService, CommentTypeAPIInterface $commentTypeAPI, UserCommentTypeAPIInterface $userCommentTypeAPI, UserTypeAPIInterface $userTypeAPI)
    {
        $this->userCommentTypeAPI = $userCommentTypeAPI;
        $this->userTypeAPI = $userTypeAPI;
        parent::__construct($translationAPI, $hooksAPI, $instanceManager, $fieldQueryInterpreter, $nameResolver, $cmsService, $semverHelperService, $commentTypeAPI);
    }
    /**
     * Execute before the upstream class
     */
    public function getPriorityToAttachToClasses() : int
    {
        return 20;
    }
    /**
     * Only use it when `mustUserBeLoggedInToAddComment`.
     * Check on runtime (not via container) since this option can be changed in WP.
     */
    public function isServiceEnabled() : bool
    {
        return ComponentConfiguration::mustUserBeLoggedInToAddComment();
    }
    public function getFieldNamesToResolve() : array
    {
        return ['authorName', 'authorURL', 'authorEmail'];
    }
    /**
     * Check there is an author. Otherwise, let the upstream resolve it
     * @param object $resultItem
     */
    public function resolveCanProcessResultItem(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = []) : bool
    {
        $comment = $resultItem;
        $commentUserID = $this->userCommentTypeAPI->getCommentUserId($comment);
        return $commentUserID !== null;
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
        $comment = $resultItem;
        $commentUserID = $this->userCommentTypeAPI->getCommentUserId($comment);
        switch ($fieldName) {
            case 'authorName':
                return $this->userTypeAPI->getUserDisplayName($commentUserID);
            case 'authorURL':
                return $this->userTypeAPI->getUserURL($commentUserID);
            case 'authorEmail':
                return $this->userTypeAPI->getUserEmail($commentUserID);
        }
        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
}
