<?php

declare (strict_types=1);
namespace PoPSchema\Users\ModuleProcessors;

use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\ModuleProcessors\DataloadQueryArgsFilterInputModuleProcessorInterface;
use PoP\ComponentModel\ModuleProcessors\DataloadQueryArgsSchemaFilterInputModuleProcessorTrait;
use PoP\ComponentModel\ModuleProcessors\DataloadQueryArgsSchemaFilterInputModuleProcessorInterface;
use PoP\ComponentModel\ModuleProcessors\AbstractFormInputModuleProcessor;
use PoPSchema\Users\FilterInputProcessors\FilterInputProcessor;
class FilterInputModuleProcessor extends AbstractFormInputModuleProcessor implements DataloadQueryArgsFilterInputModuleProcessorInterface, DataloadQueryArgsSchemaFilterInputModuleProcessorInterface
{
    use DataloadQueryArgsSchemaFilterInputModuleProcessorTrait;
    public const MODULE_FILTERINPUT_NAME = 'filterinput-name';
    public const MODULE_FILTERINPUT_EMAILS = 'filterinput-emails';
    public function getModulesToProcess() : array
    {
        return array([self::class, self::MODULE_FILTERINPUT_NAME], [self::class, self::MODULE_FILTERINPUT_EMAILS]);
    }
    public function getFilterInput(array $module) : ?array
    {
        $filterInputs = [self::MODULE_FILTERINPUT_NAME => [FilterInputProcessor::class, FilterInputProcessor::FILTERINPUT_NAME], self::MODULE_FILTERINPUT_EMAILS => [FilterInputProcessor::class, FilterInputProcessor::FILTERINPUT_EMAILS]];
        return $filterInputs[$module[1]] ?? null;
    }
    public function getName(array $module)
    {
        switch ($module[1]) {
            case self::MODULE_FILTERINPUT_NAME:
            case self::MODULE_FILTERINPUT_EMAILS:
                // Add a nice name, so that the URL params when filtering make sense
                $names = array(self::MODULE_FILTERINPUT_NAME => 'nombre', self::MODULE_FILTERINPUT_EMAILS => 'emails');
                return $names[$module[1]];
        }
        return parent::getName($module);
    }
    public function getSchemaFilterInputType(array $module) : string
    {
        switch ($module[1]) {
            case self::MODULE_FILTERINPUT_NAME:
                return SchemaDefinition::TYPE_STRING;
            case self::MODULE_FILTERINPUT_EMAILS:
                return SchemaDefinition::TYPE_EMAIL;
            default:
                return $this->getDefaultSchemaFilterInputType();
        }
    }
    public function getSchemaFilterInputIsArrayType(array $module) : bool
    {
        switch ($module[1]) {
            case self::MODULE_FILTERINPUT_EMAILS:
                return \true;
            default:
                return \false;
        }
    }
    public function getSchemaFilterInputIsNonNullableItemsInArrayType(array $module) : bool
    {
        switch ($module[1]) {
            case self::MODULE_FILTERINPUT_EMAILS:
                return \true;
            default:
                return \false;
        }
    }
    public function getSchemaFilterInputDescription(array $module) : ?string
    {
        $descriptions = [self::MODULE_FILTERINPUT_NAME => $this->translationAPI->__('Search users whose name contains this string', 'pop-users'), self::MODULE_FILTERINPUT_EMAILS => $this->translationAPI->__('Search users with any of the provided emails', 'pop-users')];
        return $descriptions[$module[1]] ?? null;
    }
}
