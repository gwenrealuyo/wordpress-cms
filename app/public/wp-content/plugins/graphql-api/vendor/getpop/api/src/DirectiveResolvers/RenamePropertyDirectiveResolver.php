<?php

declare (strict_types=1);
namespace PoP\API\DirectiveResolvers;

use PoP\ComponentModel\Directives\DirectiveTypes;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
class RenamePropertyDirectiveResolver extends \PoP\API\DirectiveResolvers\DuplicatePropertyDirectiveResolver
{
    public function getDirectiveName() : string
    {
        return 'renameProperty';
    }
    /**
     * This is a "Scripting" type directive
     */
    public function getDirectiveType() : string
    {
        return DirectiveTypes::SCRIPTING;
    }
    public function getSchemaDirectiveDescription(TypeResolverInterface $typeResolver) : ?string
    {
        return $this->translationAPI->__('Rename a property in the current object', 'component-model');
    }
    /**
     * Rename a property from the current object
     */
    public function resolveDirective(TypeResolverInterface $typeResolver, array &$idsDataFields, array &$succeedingPipelineIDsDataFields, array &$succeedingPipelineDirectiveResolverInstances, array &$resultIDItems, array &$unionDBKeyIDs, array &$dbItems, array &$previousDBItems, array &$variables, array &$messages, array &$dbErrors, array &$dbWarnings, array &$dbDeprecations, array &$dbNotices, array &$dbTraces, array &$schemaErrors, array &$schemaWarnings, array &$schemaDeprecations, array &$schemaNotices, array &$schemaTraces) : void
    {
        // After duplicating the property, delete the original
        parent::resolveDirective($typeResolver, $idsDataFields, $succeedingPipelineIDsDataFields, $succeedingPipelineDirectiveResolverInstances, $resultIDItems, $unionDBKeyIDs, $dbItems, $previousDBItems, $variables, $messages, $dbErrors, $dbWarnings, $dbDeprecations, $dbNotices, $dbTraces, $schemaErrors, $schemaWarnings, $schemaDeprecations, $schemaNotices, $schemaTraces);
        foreach ($idsDataFields as $id => $dataFields) {
            foreach ($dataFields['direct'] as $field) {
                $fieldOutputKey = $this->fieldQueryInterpreter->getFieldOutputKey($field);
                unset($dbItems[(string) $id][$fieldOutputKey]);
            }
        }
    }
}
