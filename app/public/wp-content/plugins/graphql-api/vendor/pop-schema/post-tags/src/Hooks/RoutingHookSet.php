<?php

declare (strict_types=1);
namespace PoPSchema\PostTags\Hooks;

use PoP\Hooks\AbstractHookSet;
use PoP\Routing\RouteHookNames;
use PoPSchema\PostTags\ComponentConfiguration;
class RoutingHookSet extends AbstractHookSet
{
    protected function init() : void
    {
        $this->hooksAPI->addAction(RouteHookNames::ROUTES, [$this, 'registerRoutes']);
    }
    public function registerRoutes(array $routes) : array
    {
        return \array_merge($routes, [ComponentConfiguration::getPostTagsRoute()]);
    }
}
