<?php

declare (strict_types=1);
namespace GraphQLByPoP\GraphQLServer\ObjectModels;

use GraphQLByPoP\GraphQLServer\ObjectModels\AbstractType;
use GraphQLByPoP\GraphQLServer\ObjectModels\NonDocumentableTypeTrait;
class ScalarType extends AbstractType
{
    use NonDocumentableTypeTrait;
    /**
     * @var string
     */
    protected $name;
    public function __construct(array &$fullSchemaDefinition, array $schemaDefinitionPath, string $name, array $customDefinition = [])
    {
        $this->name = $name;
        parent::__construct($fullSchemaDefinition, $schemaDefinitionPath, $customDefinition);
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getKind() : string
    {
        return \GraphQLByPoP\GraphQLServer\ObjectModels\TypeKinds::SCALAR;
    }
}
