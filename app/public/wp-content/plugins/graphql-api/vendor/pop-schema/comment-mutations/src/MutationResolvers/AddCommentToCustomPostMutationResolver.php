<?php

declare (strict_types=1);
namespace PoPSchema\CommentMutations\MutationResolvers;

use PoP\ComponentModel\ErrorHandling\Error;
use PoP\ComponentModel\Misc\GeneralUtils;
use PoP\ComponentModel\MutationResolvers\AbstractMutationResolver;
use PoP\ComponentModel\State\ApplicationState;
use PoP\Hooks\HooksAPIInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\CommentMutations\TypeAPIs\CommentTypeMutationAPIInterface;
use PoPSchema\Comments\ComponentConfiguration as CommentsComponentConfiguration;
use PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface;
use PoPSchema\Users\TypeAPIs\UserTypeAPIInterface;
use PoPSchema\UserStateMutations\MutationResolvers\ValidateUserLoggedInMutationResolverTrait;
/**
 * Add a comment to a custom post. Currently, the user must be logged-in.
 * @todo: Support non-logged-in users to add comments (check `CommentsComponentConfiguration::mustUserBeLoggedInToAddComment()`)
 */
class AddCommentToCustomPostMutationResolver extends AbstractMutationResolver
{
    use ValidateUserLoggedInMutationResolverTrait;
    /**
     * @var \PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface
     */
    protected $commentTypeAPI;
    /**
     * @var \PoPSchema\CommentMutations\TypeAPIs\CommentTypeMutationAPIInterface
     */
    protected $commentTypeMutationAPI;
    /**
     * @var \PoPSchema\Users\TypeAPIs\UserTypeAPIInterface
     */
    protected $userTypeAPI;
    public function __construct(TranslationAPIInterface $translationAPI, HooksAPIInterface $hooksAPI, CommentTypeAPIInterface $commentTypeAPI, CommentTypeMutationAPIInterface $commentTypeMutationAPI, UserTypeAPIInterface $userTypeAPI)
    {
        $this->commentTypeAPI = $commentTypeAPI;
        $this->commentTypeMutationAPI = $commentTypeMutationAPI;
        $this->userTypeAPI = $userTypeAPI;
        parent::__construct($translationAPI, $hooksAPI);
    }
    public function validateErrors(array $form_data) : ?array
    {
        $errors = [];
        // Check that the user is logged-in
        $this->validateUserIsLoggedIn($errors);
        if ($errors) {
            return $errors;
        }
        // Either provide the customPostID, or retrieve it from the parent comment
        if ((!isset($form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::CUSTOMPOST_ID]) || !$form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::CUSTOMPOST_ID]) && (!isset($form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::PARENT_COMMENT_ID]) || !$form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::PARENT_COMMENT_ID])) {
            $errors[] = $this->translationAPI->__('The custom post ID is missing.', 'comment-mutations');
        }
        if (!isset($form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::COMMENT]) || !$form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::COMMENT]) {
            $errors[] = $this->translationAPI->__('The comment is empty.', 'comment-mutations');
        }
        return $errors;
    }
    /**
     * @param string|int $comment_id
     */
    protected function additionals($comment_id, array $form_data) : void
    {
        $this->hooksAPI->doAction('gd_addcomment', $comment_id, $form_data);
    }
    protected function getCommentData(array $form_data) : array
    {
        $comment_data = ['author-IP' => $_SERVER['REMOTE_ADDR'], 'agent' => $_SERVER['HTTP_USER_AGENT'], 'content' => $form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::COMMENT], 'parent' => $form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::PARENT_COMMENT_ID], 'customPostID' => $form_data[\PoPSchema\CommentMutations\MutationResolvers\MutationInputProperties::CUSTOMPOST_ID]];
        if (CommentsComponentConfiguration::mustUserBeLoggedInToAddComment()) {
            $vars = ApplicationState::getVars();
            $user_id = $vars['global-userstate']['current-user-id'];
            $comment_data['userID'] = $user_id;
            $comment_data['author'] = $this->userTypeAPI->getUserDisplayName($user_id);
            $comment_data['authorEmail'] = $this->userTypeAPI->getUserEmail($user_id);
            $comment_data['author-URL'] = $this->userTypeAPI->getUserURL($user_id);
        } else {
            // @todo Implement!
            // $comment_data['author'] = $form_data[MutationInputProperties::AUTHOR_NAME];
            // $comment_data['authorEmail'] = $form_data[MutationInputProperties::AUTHOR_EMAIL];
        }
        // If the parent comment is provided and the custom post is not,
        // then retrieve it from there
        if (isset($comment_data['parent']) && !isset($comment_data['customPostID'])) {
            $parentComment = $this->commentTypeAPI->getComment($comment_data['parent']);
            $comment_data['customPostID'] = $this->commentTypeAPI->getCommentPostId($parentComment);
        }
        return $comment_data;
    }
    /**
     * @return string|int|\PoP\ComponentModel\ErrorHandling\Error
     */
    protected function insertComment(array $comment_data)
    {
        return $this->commentTypeMutationAPI->insertComment($comment_data);
    }
    /**
     * @return mixed
     */
    public function execute(array $form_data)
    {
        $comment_data = $this->getCommentData($form_data);
        $comment_id = $this->insertComment($comment_data);
        if (GeneralUtils::isError($comment_id)) {
            return $comment_id;
        }
        // Allow for additional operations (eg: set Action categories)
        $this->additionals($comment_id, $form_data);
        return $comment_id;
    }
}
