<?php

declare(strict_types=1);

namespace PoPSchema\CommentsWP\TypeAPIs;

use PoP\ComponentModel\TypeDataResolvers\InjectedFilterDataloadingModuleTypeDataResolverTrait;
use PoP\Hooks\HooksAPIInterface;
use PoPSchema\Comments\ComponentConfiguration as CommentsComponentConfiguration;
use PoPSchema\Comments\Constants\Status;
use PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface;
use PoPSchema\SchemaCommons\DataLoading\ReturnTypes;
use WP_Comment;

/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
class CommentTypeAPI implements CommentTypeAPIInterface
{
    use InjectedFilterDataloadingModuleTypeDataResolverTrait;

    /**
     * @var mixed[]
     */
    protected $cmsToPoPCommentStatusConversion = [
        'approve' => Status::APPROVED,
        'hold' => Status::ONHOLD,
        'spam' => Status::SPAM,
        'trash' => Status::TRASH,
    ];

    /**
     * @var mixed[]
     */
    protected $popToCMSCommentStatusConversion;
    /**
     * @var \PoP\Hooks\HooksAPIInterface
     */
    protected $hooksAPI;
    public function __construct(HooksAPIInterface $hooksAPI)
    {
        $this->hooksAPI = $hooksAPI;
        $this->popToCMSCommentStatusConversion = array_flip($this->cmsToPoPCommentStatusConversion);
    }

    /**
     * Indicates if the passed object is of type Comment
     * @param object $object
     */
    public function isInstanceOfCommentType($object): bool
    {
        return $object instanceof WP_Comment;
    }

    protected function convertCommentStatusFromCMSToPoP($status)
    {
        // Convert from the CMS status to PoP's one
        return $this->cmsToPoPCommentStatusConversion[$status];
    }
    protected function convertCommentStatusFromPoPToCMS($status)
    {
        // Convert from the CMS status to PoP's one
        return $this->popToCMSCommentStatusConversion[$status];
    }
    public function getComments(array $query, array $options = []): array
    {
        if ($return_type = $options['return-type'] ?? null) {
            if ($return_type == ReturnTypes::IDS) {
                $query['fields'] = 'ids';
            }
        }

        // Accept field atts to filter the API fields
        $this->maybeFilterDataloadQueryArgs($query, $options);

        // Convert the parameters
        if (isset($query['status'])) {
            $query['status'] = $this->convertCommentStatusFromPoPToCMS($query['status']);
        }
        if (isset($query['include'])) {
            $query['comment__in'] = $query['include'];
            unset($query['include']);
        }
        if (isset($query['customPostID'])) {
            $query['post_id'] = $query['customPostID'];
            unset($query['customPostID']);
        }
        // Comment parent ID
        // Pass "0" to retrieve 1st layer of comments added to the post
        if (isset($query['parentID'])) {
            $query['parent'] = $query['parentID'];
            unset($query['parentID']);
        }

        if (CommentsComponentConfiguration::mustUserBeLoggedInToAddComment()) {
            if (isset($query['userID'])) {
                $query['user_id'] = $query['userID'];
                unset($query['userID']);
            }
            if (isset($query['authors'])) {
                // Only 1 author is accepted
                $query['user_id'] = $query['authors'][0];
                unset($query['authors']);
            }
        }
        if (isset($query['order'])) {
            // Same param name, so do nothing
        }
        if (isset($query['orderby'])) {
            // Same param name, so do nothing
            // This param can either be a string or an array. Eg:
            // $query['orderby'] => array('date' => 'DESC', 'title' => 'ASC');
        }
        // For the comments, if there's no limit then it brings all results
        if (isset($query['limit'])) {
            $query['number'] = (int) $query['limit'];
            unset($query['limit']);
        }
        if (isset($query['search'])) {
            // Same param name, so do nothing
        }
        // Filtering by date: Instead of operating on the query, it does it through filter 'posts_where'
        if (isset($query['date-from'])) {
            $query['date_query'][] = [
                'after' => $query['date-from'],
                'inclusive' => false,
            ];
            unset($query['date-from']);
        }
        if (isset($query['date-from-inclusive'])) {
            $query['date_query'][] = [
                'after' => $query['date-from-inclusive'],
                'inclusive' => true,
            ];
            unset($query['date-from-inclusive']);
        }
        if (isset($query['date-to'])) {
            $query['date_query'][] = [
                'before' => $query['date-to'],
                'inclusive' => false,
            ];
            unset($query['date-to']);
        }
        if (isset($query['date-to-inclusive'])) {
            $query['date_query'][] = [
                'before' => $query['date-to-inclusive'],
                'inclusive' => true,
            ];
            unset($query['date-to-inclusive']);
        }
        // Only comments, no trackbacks or pingbacks
        $query['type'] = 'comment';

        $query = $this->hooksAPI->applyFilters(
            'CMSAPI:comments:query',
            $query,
            $options
        );
        return (array) \get_comments($query);
    }
    /**
     * @param string|int $comment_id
     * @return object|null
     */
    public function getComment($comment_id)
    {
        return \get_comment($comment_id);
    }
    /**
     * @param string|int $post_id
     */
    public function getCommentNumber($post_id): int
    {
        return (int) \get_comments_number($post_id);
    }
    /**
     * @param string|int $post_id
     */
    public function areCommentsOpen($post_id): bool
    {
        return \comments_open($post_id);
    }

    /**
     * @param object $comment
     */
    public function getCommentContent($comment): string
    {
        return $this->hooksAPI->applyFilters(
            'comment_text',
            $this->getCommentPlainContent($comment)
        );
    }
    /**
     * @param object $comment
     */
    public function getCommentPlainContent($comment): string
    {
        /** @var WP_Comment */
        $comment = $comment;
        return $comment->comment_content;
    }
    /**
     * @return string|int
     * @param object $comment
     */
    public function getCommentPostId($comment)
    {
        /** @var WP_Comment */
        $comment = $comment;
        return (int)$comment->comment_post_ID;
    }
    /**
     * @param object $comment
     */
    public function isCommentApproved($comment): bool
    {
        /** @var WP_Comment */
        $comment = $comment;
        return $comment->comment_approved == "1";
    }
    /**
     * @param object $comment
     */
    public function getCommentType($comment): string
    {
        /** @var WP_Comment */
        $comment = $comment;
        return $comment->comment_type;
    }
    /**
     * @return string|int|null
     * @param object $comment
     */
    public function getCommentParent($comment)
    {
        /** @var WP_Comment */
        $comment = $comment;
        // If it has no parent, it is assigned 0. In that case, return null
        if ($parent = $comment->comment_parent) {
            return (int)$parent;
        }
        return null;
    }
    /**
     * @param object $comment
     */
    public function getCommentDateGmt($comment): string
    {
        /** @var WP_Comment */
        $comment = $comment;
        return $comment->comment_date_gmt;
    }
    /**
     * @return string|int
     * @param object $comment
     */
    public function getCommentId($comment)
    {
        /** @var WP_Comment */
        $comment = $comment;
        return (int)$comment->comment_ID;
    }
    /**
     * @param object $comment
     */
    public function getCommentAuthorName($comment): string
    {
        /** @var WP_Comment */
        $comment = $comment;
        return $comment->comment_author;
    }
    /**
     * @param object $comment
     */
    public function getCommentAuthorEmail($comment): string
    {
        /** @var WP_Comment */
        $comment = $comment;
        return $comment->comment_author_email;
    }
    /**
     * @param object $comment
     */
    public function getCommentAuthorURL($comment): string
    {
        /** @var WP_Comment */
        $comment = $comment;
        return $comment->comment_author_url;
    }
}
