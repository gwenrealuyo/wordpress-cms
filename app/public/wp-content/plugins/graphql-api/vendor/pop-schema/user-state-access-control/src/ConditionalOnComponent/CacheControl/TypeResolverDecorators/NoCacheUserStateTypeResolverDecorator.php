<?php

declare (strict_types=1);
namespace PoPSchema\UserStateAccessControl\ConditionalOnComponent\CacheControl\TypeResolverDecorators;

use PoP\CacheControl\Helpers\CacheControlHelper;
use PoP\ComponentModel\DirectiveResolvers\DirectiveResolverInterface;
use PoP\ComponentModel\TypeResolverDecorators\AbstractTypeResolverDecorator;
use PoP\ComponentModel\TypeResolvers\AbstractTypeResolver;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoPSchema\UserStateAccessControl\DirectiveResolvers\ValidateIsUserLoggedInDirectiveResolver;
use PoPSchema\UserStateAccessControl\DirectiveResolvers\ValidateIsUserLoggedInForDirectivesDirectiveResolver;
use PoPSchema\UserStateAccessControl\DirectiveResolvers\ValidateIsUserNotLoggedInDirectiveResolver;
use PoPSchema\UserStateAccessControl\DirectiveResolvers\ValidateIsUserNotLoggedInForDirectivesDirectiveResolver;
class NoCacheUserStateTypeResolverDecorator extends AbstractTypeResolverDecorator
{
    public function getClassesToAttachTo() : array
    {
        return array(AbstractTypeResolver::class);
    }
    /**
     * If validating if the user is logged-in, then we can't cache the response
     */
    public function getPrecedingMandatoryDirectivesForDirectives(TypeResolverInterface $typeResolver) : array
    {
        $noCacheControlDirective = CacheControlHelper::getNoCacheDirective();
        /** @var DirectiveResolverInterface */
        $validateIsUserLoggedInDirectiveResolver = $this->instanceManager->getInstance(ValidateIsUserLoggedInDirectiveResolver::class);
        /** @var DirectiveResolverInterface */
        $validateIsUserLoggedInForDirectivesDirectiveResolver = $this->instanceManager->getInstance(ValidateIsUserLoggedInForDirectivesDirectiveResolver::class);
        /** @var DirectiveResolverInterface */
        $validateIsUserNotLoggedInDirectiveResolver = $this->instanceManager->getInstance(ValidateIsUserNotLoggedInDirectiveResolver::class);
        /** @var DirectiveResolverInterface */
        $validateIsUserNotLoggedInForDirectivesDirectiveResolver = $this->instanceManager->getInstance(ValidateIsUserNotLoggedInForDirectivesDirectiveResolver::class);
        return [$validateIsUserLoggedInDirectiveResolver->getDirectiveName() => [$noCacheControlDirective], $validateIsUserLoggedInForDirectivesDirectiveResolver->getDirectiveName() => [$noCacheControlDirective], $validateIsUserNotLoggedInDirectiveResolver->getDirectiveName() => [$noCacheControlDirective], $validateIsUserNotLoggedInForDirectivesDirectiveResolver->getDirectiveName() => [$noCacheControlDirective]];
    }
}
