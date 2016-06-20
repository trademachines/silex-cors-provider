<?php

/*
 * This file is part of the Silex CORS Provider library.
 *
 * (c) Trade Machines FI GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trademachines\Silex\Provider\Cors\Tests;

use Silex\Route;
use Trademachines\Silex\Provider\Cors\Cors;
use Trademachines\Silex\Provider\Cors\CorsControllerCollection;
use Trademachines\Silex\Provider\Cors\CorsRoute;

class CorsRouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A controller collection needs to be set
     */
    public function testNeedsAControllerCollection()
    {
        $route = new CorsRoute();
        $route->cors(new Cors());
    }

    public function testMatchOptionsMethod()
    {
        $controllerCollection = new CorsControllerCollection(new CorsRoute());
        $route                = new CorsRoute();
        $route->setControllerCollection($controllerCollection);
        $route->cors(new Cors());

        $corsRoute = $this->getCorsRouteFromCollection($controllerCollection);

        self::assertEquals(['OPTIONS'], $corsRoute->getMethods());
    }

    public function testUseSameSettings()
    {
        $path         = '/some/path';
        $requirements = ['route' => 'requirements'];

        $controllerCollection = new CorsControllerCollection(new CorsRoute());
        $route                = new CorsRoute();
        $route->setPath($path);
        $route->setRequirements($requirements);
        $route->setControllerCollection($controllerCollection);
        $route->cors(new Cors());

        $corsRoute = $this->getCorsRouteFromCollection($controllerCollection);

        self::assertEquals($path, $corsRoute->getPath());
        self::assertEquals($requirements, $corsRoute->getRequirements());
    }
    
    public function testFilterMagicRequirements()
    {
        $path         = '/some/path';
        $requirements = ['_route' => 'requirements'];

        $controllerCollection = new CorsControllerCollection(new CorsRoute());
        $route                = new CorsRoute();
        $route->setPath($path);
        $route->setRequirements($requirements);
        $route->setControllerCollection($controllerCollection);
        $route->cors(new Cors());

        $corsRoute = $this->getCorsRouteFromCollection($controllerCollection);

        self::assertEquals($path, $corsRoute->getPath());
        self::assertEmpty($corsRoute->getRequirements());
    }

    public function testHoldReferenceToInitialRoute()
    {
        $controllerCollection = new CorsControllerCollection(new CorsRoute());
        $route                = new CorsRoute();
        $route->setControllerCollection($controllerCollection);
        $route->cors(new Cors());

        $corsRoute = $this->getCorsRouteFromCollection($controllerCollection);

        self::assertSame($route, $corsRoute->getRealRoute());
    }

    public function testAddMiddleware()
    {
        $controllerCollection = new CorsControllerCollection(new CorsRoute());
        $route                = new CorsRoute();
        $route->setControllerCollection($controllerCollection);
        $route->cors(new Cors());

        self::assertCount(1, $route->getOption('_after_middlewares'));
    }
    /**
     * @param CorsControllerCollection $controllerCollection
     *
     * @return CorsRoute
     */
    private function getCorsRouteFromCollection(CorsControllerCollection $controllerCollection)
    {
        return current($controllerCollection->flush()->getIterator());
    }
}
