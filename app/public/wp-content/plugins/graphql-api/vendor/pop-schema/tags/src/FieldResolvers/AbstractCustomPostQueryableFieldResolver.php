<?php

declare (strict_types=1);
namespace PoPSchema\Tags\FieldResolvers;

use PoP\ComponentModel\FieldResolvers\AbstractQueryableFieldResolver;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\Schema\SchemaTypeModifiers;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoPSchema\SchemaCommons\DataLoading\ReturnTypes;
use PoPSchema\Tags\ComponentConfiguration;
use PoPSchema\Tags\ComponentContracts\TagAPIRequestedContractTrait;
abstract class AbstractCustomPostQueryableFieldResolver extends AbstractQueryableFieldResolver
{
    use TagAPIRequestedContractTrait;
    public function getFieldNamesToResolve() : array
    {
        return ['tags', 'tagCount', 'tagNames'];
    }
    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName) : string
    {
        $types = ['tags' => SchemaDefinition::TYPE_ID, 'tagCount' => SchemaDefinition::TYPE_INT, 'tagNames' => SchemaDefinition::TYPE_STRING];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }
    public function getSchemaFieldTypeModifiers(TypeResolverInterface $typeResolver, string $fieldName) : ?int
    {
        switch ($fieldName) {
            case 'tagCount':
                return SchemaTypeModifiers::NON_NULLABLE;
            case 'tags':
            case 'tagNames':
                return SchemaTypeModifiers::NON_NULLABLE | SchemaTypeModifiers::IS_ARRAY;
            default:
                return parent::getSchemaFieldTypeModifiers($typeResolver, $fieldName);
        }
    }
    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        $descriptions = ['tags' => $this->translationAPI->__('Tags added to this custom post', 'pop-tags'), 'tagCount' => $this->translationAPI->__('Number of tags added to this custom post', 'pop-tags'), 'tagNames' => $this->translationAPI->__('Names of the tags added to this custom post', 'pop-tags')];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }
    public function getSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName) : array
    {
        $schemaFieldArgs = parent::getSchemaFieldArgs($typeResolver, $fieldName);
        switch ($fieldName) {
            case 'tags':
            case 'tagCount':
            case 'tagNames':
                return \array_merge($schemaFieldArgs, $this->getFieldArgumentsSchemaDefinitions($typeResolver, $fieldName));
        }
        return $schemaFieldArgs;
    }
    public function enableOrderedSchemaFieldArgs(TypeResolverInterface $typeResolver, string $fieldName) : bool
    {
        switch ($fieldName) {
            case 'tags':
            case 'tagCount':
            case 'tagNames':
                return \false;
        }
        return parent::enableOrderedSchemaFieldArgs($typeResolver, $fieldName);
    }
    /**
     * @param array<string, mixed> $fieldArgs
     * @param array<string, mixed>|null $variables
     * @param array<string, mixed>|null $expressions
     * @param array<string, mixed> $options
     * @return mixed
     * @param object $resultItem
     */
    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
    {
        $tagTypeAPI = $this->getTypeAPI();
        $customPost = $resultItem;
        switch ($fieldName) {
            case 'tags':
            case 'tagNames':
                $query = ['limit' => ComponentConfiguration::getTagListDefaultLimit()];
                $options = ['return-type' => $fieldName === 'tags' ? ReturnTypes::IDS : ReturnTypes::NAMES];
                $this->addFilterDataloadQueryArgs($options, $typeResolver, $fieldName, $fieldArgs);
                return $tagTypeAPI->getCustomPostTags($typeResolver->getID($customPost), $query, $options);
            case 'tagCount':
                $options = [];
                $this->addFilterDataloadQueryArgs($options, $typeResolver, $fieldName, $fieldArgs);
                return $tagTypeAPI->getCustomPostTagCount($typeResolver->getID($customPost), [], $options);
        }
        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
    public function resolveFieldTypeResolverClass(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        switch ($fieldName) {
            case 'tags':
                return $this->getTypeResolverClass();
        }
        return parent::resolveFieldTypeResolverClass($typeResolver, $fieldName);
    }
}
