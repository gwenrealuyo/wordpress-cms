<?php

declare (strict_types=1);
namespace PoPSchema\CustomPostMediaMutations\MutationResolvers;

use PoP\ComponentModel\MutationResolvers\AbstractMutationResolver;
use PoPSchema\CustomPostMediaMutations\Facades\CustomPostMediaTypeMutationAPIFacade;
use PoPSchema\UserStateMutations\MutationResolvers\ValidateUserLoggedInMutationResolverTrait;
class SetFeaturedImageOnCustomPostMutationResolver extends AbstractMutationResolver
{
    use ValidateUserLoggedInMutationResolverTrait;
    /**
     * @return mixed
     */
    public function execute(array $form_data)
    {
        $customPostID = $form_data[\PoPSchema\CustomPostMediaMutations\MutationResolvers\MutationInputProperties::CUSTOMPOST_ID];
        $mediaItemID = $form_data[\PoPSchema\CustomPostMediaMutations\MutationResolvers\MutationInputProperties::MEDIA_ITEM_ID];
        $customPostMediaTypeMutationAPI = CustomPostMediaTypeMutationAPIFacade::getInstance();
        $customPostMediaTypeMutationAPI->setFeaturedImage($customPostID, $mediaItemID);
        return $customPostID;
    }
    public function validateErrors(array $form_data) : ?array
    {
        $errors = [];
        // Check that the user is logged-in
        $this->validateUserIsLoggedIn($errors);
        if ($errors) {
            return $errors;
        }
        if (!$form_data[\PoPSchema\CustomPostMediaMutations\MutationResolvers\MutationInputProperties::CUSTOMPOST_ID]) {
            $errors[] = $this->translationAPI->__('The custom post ID is missing.', 'custompostmedia-mutations');
        }
        if (!$form_data[\PoPSchema\CustomPostMediaMutations\MutationResolvers\MutationInputProperties::MEDIA_ITEM_ID]) {
            $errors[] = $this->translationAPI->__('The media item ID is missing.', 'custompostmedia-mutations');
        }
        return $errors;
    }
}
