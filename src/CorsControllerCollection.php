<?php

/*
 * This file is part of the Silex CORS Provider library.
 *
 * (c) Trade Machines FI GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trademachines\Silex\Provider\Cors;

use Silex\ControllerCollection;

/**
 * Adds necessary dependencies to routes.
 */
class CorsControllerCollection extends ControllerCollection
{
    public function match($pattern, $to = null)
    {
        $controller = parent::match($pattern, $to);
        $route = $controller->getRoute();

        if (!$route instanceof CorsRoute) {
            throw new \RuntimeException(
                'The generated route does not have the proper type, did you register the service provider properly?'
            );
        }
        
        $route->setControllerCollection($this);
        
        return $controller;
    }
}
