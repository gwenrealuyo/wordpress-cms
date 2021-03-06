<?php

declare(strict_types=1);

namespace PoPSchema\UsersWP\ConditionalOnComponent\CustomPosts\TypeAPIs;

use PoPSchema\CustomPostsWP\TypeAPIs\CustomPostTypeAPIHelpers;
use PoPSchema\Users\ConditionalOnComponent\CustomPosts\TypeAPIs\CustomPostUserTypeAPIInterface;

/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
class CustomPostUserTypeAPI implements CustomPostUserTypeAPIInterface
{
    /**
     * @param string|int|object $customPostObjectOrID
     */
    public function getAuthorID($customPostObjectOrID)
    {
        list(
            $customPost,
            $customPostID,
        ) = CustomPostTypeAPIHelpers::getCustomPostObjectAndID($customPostObjectOrID);
        return $customPost->post_author;
    }
}
