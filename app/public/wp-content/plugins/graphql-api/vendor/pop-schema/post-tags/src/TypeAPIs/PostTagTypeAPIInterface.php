<?php

declare (strict_types=1);
namespace PoPSchema\PostTags\TypeAPIs;

use PoPSchema\Tags\TypeAPIs\TagTypeAPIInterface;
/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
interface PostTagTypeAPIInterface extends TagTypeAPIInterface
{
    /**
     * Indicates if the passed object is of type PostTag
     * @param object $object
     */
    public function isInstanceOfPostTagType($object) : bool;
    /**
     * The taxonomy name representing a post tag ("post_tag")
     */
    public function getPostTagTaxonomyName() : string;
}
