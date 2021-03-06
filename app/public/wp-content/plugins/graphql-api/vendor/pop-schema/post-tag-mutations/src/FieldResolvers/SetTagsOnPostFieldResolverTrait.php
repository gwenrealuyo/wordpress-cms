<?php

declare (strict_types=1);
namespace PoPSchema\PostTagMutations\FieldResolvers;

use PoP\Translation\Facades\TranslationAPIFacade;
use PoPSchema\Posts\TypeResolvers\PostTypeResolver;
use PoPSchema\PostTagMutations\MutationResolvers\SetTagsOnPostMutationResolver;
trait SetTagsOnPostFieldResolverTrait
{
    protected function getTypeResolverClass() : string
    {
        return PostTypeResolver::class;
    }
    protected function getTypeMutationResolverClass() : string
    {
        return SetTagsOnPostMutationResolver::class;
    }
    protected function getEntityName() : string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        return $translationAPI->__('post', 'post-tag-mutations');
    }
}
