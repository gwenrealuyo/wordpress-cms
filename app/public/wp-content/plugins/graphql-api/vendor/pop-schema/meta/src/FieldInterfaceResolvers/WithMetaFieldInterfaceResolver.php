<?php

declare (strict_types=1);
namespace PoPSchema\Meta\FieldInterfaceResolvers;

use PoP\ComponentModel\FieldInterfaceResolvers\AbstractSchemaFieldInterfaceResolver;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\SchemaTypeModifiers;
class WithMetaFieldInterfaceResolver extends AbstractSchemaFieldInterfaceResolver
{
    public function getInterfaceName() : string
    {
        return 'WithMeta';
    }
    public function getSchemaInterfaceDescription() : ?string
    {
        return $this->translationAPI->__('Fields with meta values', 'custompostmeta');
    }
    public function getFieldNamesToImplement() : array
    {
        return ['metaValue', 'metaValues'];
    }
    public function getSchemaFieldType(string $fieldName) : string
    {
        $types = ['metaValue' => SchemaDefinition::TYPE_ANY_SCALAR, 'metaValues' => SchemaDefinition::TYPE_ANY_SCALAR];
        return $types[$fieldName] ?? parent::getSchemaFieldType($fieldName);
    }
    public function getSchemaFieldTypeModifiers(string $fieldName) : ?int
    {
        switch ($fieldName) {
            case 'metaValues':
                return SchemaTypeModifiers::IS_ARRAY;
            default:
                return parent::getSchemaFieldTypeModifiers($fieldName);
        }
    }
    public function getSchemaFieldArgs(string $fieldName) : array
    {
        $schemaFieldArgs = parent::getSchemaFieldArgs($fieldName);
        switch ($fieldName) {
            case 'metaValue':
            case 'metaValues':
                return \array_merge($schemaFieldArgs, [[SchemaDefinition::ARGNAME_NAME => 'key', SchemaDefinition::ARGNAME_TYPE => SchemaDefinition::TYPE_STRING, SchemaDefinition::ARGNAME_DESCRIPTION => $this->translationAPI->__('The meta key', 'meta'), SchemaDefinition::ARGNAME_MANDATORY => \true]]);
        }
        return $schemaFieldArgs;
    }
    public function getSchemaFieldDescription(string $fieldName) : ?string
    {
        $descriptions = ['metaValue' => $this->translationAPI->__('Single meta value', 'custompostmeta'), 'metaValues' => $this->translationAPI->__('List of meta values', 'custompostmeta')];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($fieldName);
    }
}
