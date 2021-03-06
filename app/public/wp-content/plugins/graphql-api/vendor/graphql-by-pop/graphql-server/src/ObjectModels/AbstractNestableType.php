<?php

declare (strict_types=1);
namespace GraphQLByPoP\GraphQLServer\ObjectModels;

use GraphQLByPoP\GraphQLServer\ObjectModels\AbstractType;
abstract class AbstractNestableType extends AbstractType
{
    /**
     * @var \GraphQLByPoP\GraphQLServer\ObjectModels\AbstractType
     */
    protected $nestedType;
    public function __construct(array &$fullSchemaDefinition, array $schemaDefinitionPath, AbstractType $nestedType, array $customDefinition = [])
    {
        $this->nestedType = $nestedType;
        parent::__construct($fullSchemaDefinition, $schemaDefinitionPath, $customDefinition);
    }
    public function getNestedType() : AbstractType
    {
        return $this->nestedType;
    }
    public function getNestedTypeID() : string
    {
        return $this->nestedType->getID();
    }
}
