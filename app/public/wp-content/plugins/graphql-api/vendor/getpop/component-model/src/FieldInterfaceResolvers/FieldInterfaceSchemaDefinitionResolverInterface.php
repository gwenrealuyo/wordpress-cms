<?php

declare (strict_types=1);
namespace PoP\ComponentModel\FieldInterfaceResolvers;

interface FieldInterfaceSchemaDefinitionResolverInterface
{
    public function getFieldNamesToResolve() : array;
    public function getSchemaFieldType(string $fieldName) : string;
    public function getSchemaFieldTypeModifiers(string $fieldName) : ?int;
    public function getSchemaFieldDescription(string $fieldName) : ?string;
    public function getSchemaFieldArgs(string $fieldName) : array;
    public function getSchemaFieldDeprecationDescription(string $fieldName, array $fieldArgs = []) : ?string;
    public function addSchemaDefinitionForField(array &$schemaDefinition, string $fieldName) : void;
}
