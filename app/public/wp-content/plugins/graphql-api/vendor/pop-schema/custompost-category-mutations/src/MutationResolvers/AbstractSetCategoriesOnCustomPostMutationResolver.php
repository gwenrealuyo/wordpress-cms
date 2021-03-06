<?php

declare (strict_types=1);
namespace PoPSchema\CustomPostCategoryMutations\MutationResolvers;

use PoP\ComponentModel\MutationResolvers\AbstractMutationResolver;
use PoPSchema\CustomPostCategoryMutations\TypeAPIs\CustomPostCategoryTypeMutationAPIInterface;
use PoPSchema\UserStateMutations\MutationResolvers\ValidateUserLoggedInMutationResolverTrait;
abstract class AbstractSetCategoriesOnCustomPostMutationResolver extends AbstractMutationResolver
{
    use ValidateUserLoggedInMutationResolverTrait;
    /**
     * @return mixed
     */
    public function execute(array $form_data)
    {
        $customPostID = $form_data[\PoPSchema\CustomPostCategoryMutations\MutationResolvers\MutationInputProperties::CUSTOMPOST_ID];
        $postCategoryIDs = $form_data[\PoPSchema\CustomPostCategoryMutations\MutationResolvers\MutationInputProperties::CATEGORY_IDS];
        $append = $form_data[\PoPSchema\CustomPostCategoryMutations\MutationResolvers\MutationInputProperties::APPEND];
        $customPostCategoryTypeAPI = $this->getCustomPostCategoryTypeMutationAPI();
        $customPostCategoryTypeAPI->setCategories($customPostID, $postCategoryIDs, $append);
        return $customPostID;
    }
    protected abstract function getCustomPostCategoryTypeMutationAPI() : CustomPostCategoryTypeMutationAPIInterface;
    public function validateErrors(array $form_data) : ?array
    {
        $errors = [];
        // Check that the user is logged-in
        $this->validateUserIsLoggedIn($errors);
        if ($errors) {
            return $errors;
        }
        if (!$form_data[\PoPSchema\CustomPostCategoryMutations\MutationResolvers\MutationInputProperties::CUSTOMPOST_ID]) {
            $errors[] = \sprintf($this->translationAPI->__('The %s ID is missing.', 'custompost-category-mutations'), $this->getEntityName());
        }
        return $errors;
    }
    protected function getEntityName() : string
    {
        return $this->translationAPI->__('custom post', 'custompost-category-mutations');
    }
}
