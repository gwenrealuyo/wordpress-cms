<?php

declare (strict_types=1);
namespace GraphQLByPoP\GraphQLServer\TypeDataLoaders;

use GraphQLByPoP\GraphQLServer\ObjectModels\Schema;
use GraphQLByPoP\GraphQLServer\TypeResolvers\SchemaTypeResolver;
use PoP\ComponentModel\TypeDataLoaders\AbstractTypeDataLoader;
use GraphQLByPoP\GraphQLServer\Facades\Registries\SchemaDefinitionReferenceRegistryFacade;
use PoP\ComponentModel\TypeDataLoaders\UseObjectDictionaryTypeDataLoaderTrait;
class SchemaTypeDataLoader extends AbstractTypeDataLoader
{
    use UseObjectDictionaryTypeDataLoaderTrait;
    protected function getTypeResolverClass() : string
    {
        return SchemaTypeResolver::class;
    }
    /**
     * @param int|string $id
     */
    protected function getTypeNewInstance($id)
    {
        return new Schema($this->getSchemaDefinition($id), (string) $id);
    }
    protected function &getSchemaDefinition(string $id) : array
    {
        $schemaDefinitionReferenceRegistry = SchemaDefinitionReferenceRegistryFacade::getInstance();
        return $schemaDefinitionReferenceRegistry->getFullSchemaDefinition();
    }
}
