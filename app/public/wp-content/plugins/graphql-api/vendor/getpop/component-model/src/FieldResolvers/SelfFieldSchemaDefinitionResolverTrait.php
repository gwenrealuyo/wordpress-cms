<?php

declare (strict_types=1);
namespace PoP\ComponentModel\FieldResolvers;

use PoP\ComponentModel\Facades\Schema\SchemaDefinitionServiceFacade;
use PoP\ComponentModel\FieldResolvers\FieldSchemaDefinitionResolverInterface;
use PoP\ComponentModel\Resolvers\WithVersionConstraintFieldOrDirectiveResolverTrait;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
trait SelfFieldSchemaDefinitionResolverTrait
{
    use WithVersionConstraintFieldOrDirectiveResolverTrait;
    /**
     * The object resolves its own schema definition
     */
    public function getSchemaDefinitionResolver(TypeResolverInterface $typeResolver) : ?FieldSchemaDefinitionResolverInterface
    {
        return $this;
    }
    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName) : string
    {
        $schemaDefinitionService = SchemaDefinitionServiceFacade::getInstance();
        return $schemaDefinitionService->getDefaultType();
    }
    /**
     * By default types are nullable, and not an array
     */
    public function getSchemaFieldTypeModifiers(TypeResolverInterface $typeResolver, string $fieldName) : ?int
    {
        return null;
    }
    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        return null;
    }
    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName) : array
    {
        return [];
    }
    public function getSchemaFieldDeprecationDescription(TypeResolverInterface $typeResolver, string $fieldName, array $fieldArgs = []) : ?string
    {
        return null;
    }
    public function addSchemaDefinitionForField(array &$schemaDefinition, TypeResolverInterface $typeResolver, string $fieldName) : void
    {
    }
}
