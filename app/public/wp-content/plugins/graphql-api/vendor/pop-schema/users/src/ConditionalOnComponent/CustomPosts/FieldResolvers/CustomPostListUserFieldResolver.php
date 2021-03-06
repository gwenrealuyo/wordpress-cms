<?php

declare (strict_types=1);
namespace PoPSchema\Users\ConditionalOnComponent\CustomPosts\FieldResolvers;

use PoPSchema\Users\TypeResolvers\UserTypeResolver;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoPSchema\CustomPosts\FieldResolvers\AbstractCustomPostListFieldResolver;
class CustomPostListUserFieldResolver extends AbstractCustomPostListFieldResolver
{
    public function getClassesToAttachTo() : array
    {
        return array(UserTypeResolver::class);
    }
    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        $descriptions = ['customPosts' => $this->translationAPI->__('Custom posts by the user', 'pop-users'), 'customPostCount' => $this->translationAPI->__('Number of custom posts by the user', 'pop-users'), 'unrestrictedCustomPosts' => $this->translationAPI->__('[Unrestricted] Custom posts by the user', 'pop-users'), 'unrestrictedCustomPostCount' => $this->translationAPI->__('[Unrestricted] Number of custom posts by the user', 'pop-users')];
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
            case 'customPosts':
            case 'customPostCount':
            case 'unrestrictedCustomPosts':
            case 'unrestrictedCustomPostCount':
                $query['authors'] = [$typeResolver->getID($user)];
                break;
        }
        return $query;
    }
}
