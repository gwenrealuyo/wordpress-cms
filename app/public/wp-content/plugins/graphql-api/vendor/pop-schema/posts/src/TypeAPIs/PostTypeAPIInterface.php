<?php

declare (strict_types=1);
namespace PoPSchema\Posts\TypeAPIs;

use PoPSchema\CustomPosts\TypeAPIs\CustomPostTypeAPIInterface;
/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
interface PostTypeAPIInterface extends CustomPostTypeAPIInterface
{
    /**
     * Indicates if the passed object is of type Post
     * @param object $object
     */
    public function isInstanceOfPostType($object) : bool;
    /**
     * Indicate if an post with provided ID exists
     * @param int|string $id
     */
    public function postExists($id) : bool;
    /**
     * Get the post with provided ID or, if it doesn't exist, null
     * @param int|string $id
     * @return object|null
     */
    public function getPost($id);
    /**
     * Get the list of posts
     */
    public function getPosts(array $query, array $options = []) : array;
    /**
     * Get the number of posts
     */
    public function getPostCount(array $query = [], array $options = []) : int;
    /**
     * Post custom post type
     */
    public function getPostCustomPostType() : string;
}
