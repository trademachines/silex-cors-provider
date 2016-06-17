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

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CorsServiceProvider implements ServiceProviderInterface
{
    /** {@inheritdoc} **/
    public function register(Container $app)
    {
        $app['route_class']   = CorsRoute::class;
        $app['cors'] = function ($app) {
            return new CorsService($app['routes']);
        };
        $controllers_factory = function () use ($app, &$controllers_factory) {
            return new CorsControllerCollection($app['route_factory'], $app['routes_factory'], $controllers_factory);
        };
        $app['controllers_factory'] = $app->factory($controllers_factory);
    }
}
