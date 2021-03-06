<?php

declare (strict_types=1);
namespace PoP\ComponentModel\Facades\ErrorHandling;

use PoP\ComponentModel\ErrorHandling\ErrorProviderInterface;
use PoP\Root\Container\ContainerBuilderFactory;
class ErrorProviderFacade
{
    public static function getInstance() : ErrorProviderInterface
    {
        /**
         * @var ErrorProviderInterface
         */
        $service = ContainerBuilderFactory::getInstance()->get(ErrorProviderInterface::class);
        return $service;
    }
}
