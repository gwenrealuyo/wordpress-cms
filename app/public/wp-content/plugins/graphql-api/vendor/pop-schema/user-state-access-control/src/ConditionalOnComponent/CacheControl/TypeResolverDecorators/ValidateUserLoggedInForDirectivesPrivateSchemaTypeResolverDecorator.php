<?php

declare (strict_types=1);
namespace PoPSchema\UserStateAccessControl\ConditionalOnComponent\CacheControl\TypeResolverDecorators;

use PoPSchema\UserStateAccessControl\TypeResolverDecorators\ValidateUserLoggedInForDirectivesTypeResolverDecoratorTrait;
class ValidateUserLoggedInForDirectivesPrivateSchemaTypeResolverDecorator extends \PoPSchema\UserStateAccessControl\ConditionalOnComponent\CacheControl\TypeResolverDecorators\AbstractNoCacheConfigurableAccessControlForDirectivesInPrivateSchemaTypeResolverDecorator
{
    use ValidateUserLoggedInForDirectivesTypeResolverDecoratorTrait;
}
