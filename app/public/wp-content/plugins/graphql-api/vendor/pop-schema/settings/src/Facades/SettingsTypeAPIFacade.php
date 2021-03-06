<?php

declare (strict_types=1);
namespace PoPSchema\Settings\Facades;

use PoPSchema\Settings\TypeAPIs\SettingsTypeAPIInterface;
use PoP\Root\Container\ContainerBuilderFactory;
class SettingsTypeAPIFacade
{
    public static function getInstance() : SettingsTypeAPIInterface
    {
        /**
         * @var SettingsTypeAPIInterface
         */
        $service = ContainerBuilderFactory::getInstance()->get(SettingsTypeAPIInterface::class);
        return $service;
    }
}
