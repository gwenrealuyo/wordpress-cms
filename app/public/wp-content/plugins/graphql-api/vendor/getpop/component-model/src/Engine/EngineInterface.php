<?php

declare (strict_types=1);
namespace PoP\ComponentModel\Engine;

use PoP\ComponentModel\ErrorHandling\Error;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
interface EngineInterface
{
    public function getOutputData() : array;
    public function addBackgroundUrl(string $url, array $targets) : void;
    public function getEntryModule() : array;
    public function sendEtagHeader() : void;
    public function getExtraRoutes() : array;
    public function listExtraRouteVars() : array;
    public function generateData() : void;
    public function calculateOutuputData() : void;
    public function getModelPropsModuletree(array $module) : array;
    public function addRequestPropsModuletree(array $module, array $props) : array;
    public function getModuleDatasetSettings(array $module, $model_props, array &$props) : array;
    public function getRequestMeta() : array;
    public function getSessionMeta() : array;
    public function getSiteMeta() : array;
    /**
     * @return bool|\PoP\ComponentModel\ErrorHandling\Error
     */
    public function validateCheckpoints(array $checkpoints);
    public function getModuleData(array $root_module, array $root_model_props, array $root_props) : array;
    public function moveEntriesUnderDBName(array $entries, bool $entryHasId, TypeResolverInterface $typeResolver) : array;
    public function getDatabases() : array;
}
