<?php

declare (strict_types=1);
namespace PoP\ComponentModel\DirectiveResolvers;

use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
abstract class AbstractSchemaDirectiveResolver extends \PoP\ComponentModel\DirectiveResolvers\AbstractDirectiveResolver implements \PoP\ComponentModel\DirectiveResolvers\SchemaDirectiveResolverInterface
{
    public function getSchemaDefinitionResolver(TypeResolverInterface $typeResolver) : ?\PoP\ComponentModel\DirectiveResolvers\SchemaDirectiveResolverInterface
    {
        return $this;
    }
    public function getSchemaDirectiveDescription(TypeResolverInterface $typeResolver) : ?string
    {
        return null;
    }
    public function getSchemaDirectiveWarningDescription(TypeResolverInterface $typeResolver) : ?string
    {
        return null;
    }
    public function getSchemaDirectiveDeprecationDescription(TypeResolverInterface $typeResolver) : ?string
    {
        return null;
    }
    public function getSchemaDirectiveExpressions(TypeResolverInterface $typeResolver) : array
    {
        return [];
    }
    public function getSchemaDirectiveArgs(TypeResolverInterface $typeResolver) : array
    {
        return [];
    }
    public function enableOrderedSchemaDirectiveArgs(TypeResolverInterface $typeResolver) : bool
    {
        return \true;
    }
    public function isGlobal(TypeResolverInterface $typeResolver) : bool
    {
        return \false;
    }
}
