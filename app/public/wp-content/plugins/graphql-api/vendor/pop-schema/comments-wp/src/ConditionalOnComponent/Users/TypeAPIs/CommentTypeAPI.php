<?php

declare(strict_types=1);

namespace PoPSchema\CommentsWP\ConditionalOnComponent\Users\TypeAPIs;

use PoPSchema\Comments\ConditionalOnComponent\Users\TypeAPIs\CommentTypeAPIInterface;
use WP_Comment;

/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
class CommentTypeAPI implements CommentTypeAPIInterface
{
    /**
     * @return string|int|null
     * @param object $comment
     */
    public function getCommentUserId($comment)
    {
        /** @var WP_Comment */
        $comment = $comment;
        // Watch out! If there is no user ID, it stores it with ID "0"
        $userID = (int)$comment->user_id;
        if ($userID === 0) {
            return null;
        }
        return $userID;
    }
}
