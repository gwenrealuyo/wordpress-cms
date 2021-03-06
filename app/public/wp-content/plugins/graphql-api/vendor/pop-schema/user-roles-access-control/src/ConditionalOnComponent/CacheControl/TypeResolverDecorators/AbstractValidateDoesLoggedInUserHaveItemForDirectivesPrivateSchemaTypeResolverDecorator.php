<?php

declare (strict_types=1);
namespace PoPSchema\UserRolesAccessControl\ConditionalOnComponent\CacheControl\TypeResolverDecorators;

use PoP\AccessControl\TypeResolverDecorators\AbstractConfigurableAccessControlForDirectivesInPrivateSchemaTypeResolverDecorator;
use PoPSchema\UserStateAccessControl\ConditionalOnComponent\CacheControl\TypeResolverDecorators\NoCacheConfigurableAccessControlTypeResolverDecoratorTrait;
abstract class AbstractValidateDoesLoggedInUserHaveItemForDirectivesPrivateSchemaTypeResolverDecorator extends AbstractConfigurableAccessControlForDirectivesInPrivateSchemaTypeResolverDecorator
{
    use NoCacheConfigurableAccessControlTypeResolverDecoratorTrait;
}
