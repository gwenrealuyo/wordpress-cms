<?php

declare (strict_types=1);
namespace PoP\Engine\Hooks;

use PoP\Hooks\AbstractHookSet;
abstract class AbstractCMSBootHookSet extends AbstractHookSet
{
    /**
     * Initialize the hooks when the CMS initializes
     */
    protected function init() : void
    {
        $this->hooksAPI->addAction('popcms:boot', [$this, 'cmsBoot'], $this->getPriority());
    }
    protected function getPriority() : int
    {
        return 10;
    }
    public abstract function cmsBoot() : void;
}
