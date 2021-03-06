<?php

declare (strict_types=1);
namespace PoP\Routing;

use PoP\Root\Component\AbstractComponent;
/**
 * Initialize component
 */
class Component extends AbstractComponent
{
    /**
     * Classes from PoP components that must be initialized before this component
     *
     * @return string[]
     */
    public static function getDependedComponentClasses() : array
    {
        return [\PoP\Hooks\Component::class, \PoP\Definitions\Component::class];
    }
    public static function beforeBoot() : void
    {
        \PoP\Routing\Routes::init();
    }
}
