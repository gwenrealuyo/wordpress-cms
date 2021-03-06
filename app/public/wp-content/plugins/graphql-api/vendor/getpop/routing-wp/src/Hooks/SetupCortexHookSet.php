<?php

declare (strict_types=1);
namespace PoP\RoutingWP\Hooks;

use PoP\Hooks\AbstractHookSet;
use PrefixedByPoP\Brain\Cortex\Route\RouteCollectionInterface;
use PrefixedByPoP\Brain\Cortex\Route\RouteInterface;
use PrefixedByPoP\Brain\Cortex\Route\QueryRoute;
use PoP\RoutingWP\WPQueries;
use PoP\Routing\Facades\RoutingManagerFacade;
class SetupCortexHookSet extends AbstractHookSet
{
    protected function init() : void
    {
        $this->hooksAPI->addAction('cortex.routes', [$this, 'setupCortex'], 1);
    }
    /**
     * @param RouteCollectionInterface<RouteInterface> $routes
     */
    public function setupCortex(RouteCollectionInterface $routes) : void
    {
        $routingManager = RoutingManagerFacade::getInstance();
        foreach ($routingManager->getRoutes() as $route) {
            $routes->addRoute(new QueryRoute($route, function (array $matches) {
                return WPQueries::STANDARD_NATURE;
            }));
        }
    }
}
