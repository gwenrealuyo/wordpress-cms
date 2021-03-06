<?php

declare (strict_types=1);
namespace PoP\ComponentModel\CheckpointProcessors;

use PoP\ComponentModel\ErrorHandling\Error;
use PoP\ComponentModel\State\ApplicationState;
use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\CheckpointProcessors\AbstractCheckpointProcessor;
class MutationCheckpointProcessor extends AbstractCheckpointProcessor
{
    public const HOOK_MUTATIONS_NOT_SUPPORTED_ERROR_MSG = __CLASS__ . ':MutationsNotSupportedErrorMsg';
    public const ENABLED_MUTATIONS = 'enabled-mutations';
    public function getCheckpointsToProcess()
    {
        return array([self::class, self::ENABLED_MUTATIONS]);
    }
    public function process(array $checkpoint)
    {
        switch ($checkpoint[1]) {
            case self::ENABLED_MUTATIONS:
                $vars = ApplicationState::getVars();
                if (!$vars['are-mutations-enabled']) {
                    $errorMessage = $this->hooksAPI->applyFilters(self::HOOK_MUTATIONS_NOT_SUPPORTED_ERROR_MSG, $this->translationAPI->__('Mutations cannot be executed', 'component-model'));
                    return new Error('mutations-not-supported', $errorMessage);
                }
                break;
        }
        return parent::process($checkpoint);
    }
}
