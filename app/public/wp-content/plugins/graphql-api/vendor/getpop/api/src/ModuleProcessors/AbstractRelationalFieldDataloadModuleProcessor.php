<?php

declare (strict_types=1);
namespace PoP\API\ModuleProcessors;

use PoP\API\Constants\Formats;
use PoP\ComponentModel\ModuleProcessors\AbstractDataloadModuleProcessor;
abstract class AbstractRelationalFieldDataloadModuleProcessor extends AbstractDataloadModuleProcessor
{
    protected function getInnerSubmodules(array $module) : array
    {
        $ret = parent::getInnerSubmodules($module);
        // The fields to retrieve are passed through module atts, so simply transfer all module atts down the line
        $ret[] = [\PoP\API\ModuleProcessors\RelationalFieldQueryDataModuleProcessor::class, \PoP\API\ModuleProcessors\RelationalFieldQueryDataModuleProcessor::MODULE_LAYOUT_RELATIONALFIELDS, $module[2] ?? null];
        return $ret;
    }
    public function getFormat(array $module) : ?string
    {
        return Formats::FIELDS;
    }
}
