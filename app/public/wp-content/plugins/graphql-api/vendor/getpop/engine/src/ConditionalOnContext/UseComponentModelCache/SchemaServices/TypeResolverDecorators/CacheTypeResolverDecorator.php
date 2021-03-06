<?php

declare (strict_types=1);
namespace PoP\Engine\ConditionalOnContext\UseComponentModelCache\SchemaServices\TypeResolverDecorators;

use PoP\ComponentModel\DirectiveResolvers\DirectiveResolverInterface;
use PoP\ComponentModel\TypeResolverDecorators\AbstractTypeResolverDecorator;
use PoP\ComponentModel\TypeResolvers\AbstractTypeResolver;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\Engine\ConditionalOnContext\UseComponentModelCache\SchemaServices\DirectiveResolvers\LoadCacheDirectiveResolver;
use PoP\Engine\ConditionalOnContext\UseComponentModelCache\SchemaServices\DirectiveResolvers\SaveCacheDirectiveResolver;
class CacheTypeResolverDecorator extends AbstractTypeResolverDecorator
{
    public function getClassesToAttachTo() : array
    {
        return array(AbstractTypeResolver::class);
    }
    /**
     * Directives @loadCache and @saveCache (called @cache) always go together
     */
    public function getPrecedingMandatoryDirectivesForDirectives(TypeResolverInterface $typeResolver) : array
    {
        /** @var DirectiveResolverInterface */
        $loadCacheDirectiveResolver = $this->instanceManager->getInstance(LoadCacheDirectiveResolver::class);
        /** @var DirectiveResolverInterface */
        $saveCacheDirectiveResolver = $this->instanceManager->getInstance(SaveCacheDirectiveResolver::class);
        $loadCacheDirective = $this->fieldQueryInterpreter->getDirective($loadCacheDirectiveResolver->getDirectiveName());
        return [$saveCacheDirectiveResolver->getDirectiveName() => [$loadCacheDirective]];
    }
}
