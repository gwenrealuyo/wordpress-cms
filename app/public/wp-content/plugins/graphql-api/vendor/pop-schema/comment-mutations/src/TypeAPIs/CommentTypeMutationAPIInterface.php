<?php

declare (strict_types=1);
namespace PoPSchema\CommentMutations\TypeAPIs;

use PoP\ComponentModel\ErrorHandling\Error;
/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
interface CommentTypeMutationAPIInterface
{
    /**
     * @return string|int|\PoP\ComponentModel\ErrorHandling\Error
     */
    public function insertComment(array $comment_data);
}
