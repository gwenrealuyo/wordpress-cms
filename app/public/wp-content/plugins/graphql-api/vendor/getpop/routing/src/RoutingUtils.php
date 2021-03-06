<?php

declare (strict_types=1);
namespace PoP\Routing;

use PoP\Hooks\Facades\HooksAPIFacade;
class RoutingUtils
{
    public static function getURLPath() : string
    {
        // Allow to remove the language information from qTranslate (https://domain.com/en/...)
        $route = HooksAPIFacade::getInstance()->applyFilters('\\PoP\\Routing:uri-route', $_SERVER['REQUEST_URI']);
        $params_pos = \strpos($route, '?');
        if ($params_pos !== \false) {
            $route = \substr($route, 0, $params_pos);
        }
        return \trim($route, '/');
    }
}
