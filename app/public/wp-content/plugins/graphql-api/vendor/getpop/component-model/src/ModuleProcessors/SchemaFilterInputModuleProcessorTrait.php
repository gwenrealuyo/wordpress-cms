<?php

declare (strict_types=1);
namespace PoP\ComponentModel\ModuleProcessors;

use PoP\ComponentModel\Facades\Schema\SchemaDefinitionServiceFacade;
trait SchemaFilterInputModuleProcessorTrait
{
    public function getSchemaFilterInputType(array $module) : string
    {
        return $this->getDefaultSchemaFilterInputType();
    }
    protected function getDefaultSchemaFilterInputType() : string
    {
        $schemaDefinitionService = SchemaDefinitionServiceFacade::getInstance();
        return $schemaDefinitionService->getDefaultType();
    }
    public function getSchemaFilterInputDescription(array $module) : ?string
    {
        return null;
    }
    public function getSchemaFilterInputDeprecationDescription(array $module) : ?string
    {
        return null;
    }
    public function getSchemaFilterInputIsArrayType(array $module) : bool
    {
        return \false;
    }
    public function getSchemaFilterInputIsNonNullableItemsInArrayType(array $module) : bool
    {
        return \false;
    }
    public function getSchemaFilterInputIsArrayOfArraysType(array $module) : bool
    {
        return \false;
    }
    public function getSchemaFilterInputIsNonNullableItemsInArrayOfArraysType(array $module) : bool
    {
        return \false;
    }
    public function getSchemaFilterInputMandatory(array $module) : bool
    {
        return \false;
    }
    public function addSchemaDefinitionForFilter(array &$schemaDefinition, array $module) : void
    {
        // Override
    }
}
