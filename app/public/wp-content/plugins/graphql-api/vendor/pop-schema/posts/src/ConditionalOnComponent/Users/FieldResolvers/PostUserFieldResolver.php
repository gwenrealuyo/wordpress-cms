<?php

declare (strict_types=1);
namespace PoPSchema\Posts\ConditionalOnComponent\Users\FieldResolvers;

use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoPSchema\Posts\FieldResolvers\AbstractPostFieldResolver;
use PoPSchema\Users\TypeResolvers\UserTypeResolver;
class PostUserFieldResolver extends AbstractPostFieldResolver
{
    public function getClassesToAttachTo() : array
    {
        return array(UserTypeResolver::class);
    }
    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        $descriptions = ['posts' => $this->translationAPI->__('Posts by the user', 'users'), 'postCount' => $this->translationAPI->__('Number of posts by the user', 'users'), 'unrestrictedPosts' => $this->translationAPI->__('[Unrestricted] Posts by the user', 'users'), 'unrestrictedPostCount' => $this->translationAPI->__('[Unrestricted] Number of posts by the user', 'users')];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }
    /**
     * @param array<string, mixed> $fieldArgs
     * @return array<string, mixed>
     * @param object $resultItem
     */
    protected function getQuery(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = []) : array
    {
        $query = parent::getQuery($typeResolver, $resultItem, $fieldName, $fieldArgs);
        $user = $resultItem;
        switch ($fieldName) {
            case 'posts':
            case 'postCount':
            case 'unrestrictedPosts':
            case 'unrestrictedPostCount':
                $query['authors'] = [$typeResolver->getID($user)];
                break;
        }
        return $query;
    }
}
