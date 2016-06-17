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
use Trademachines\Silex\Provider\Cors\CorsControllerCollection;
use Trademachines\Silex\Provider\Cors\CorsRoute;

class CorsControllerCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The generated route does not have the proper type
     */
    public function testThrowExceptionOnWrongRoute()
    {
        $collection = new CorsControllerCollection(new Route());
        $collection->get('/');
    }

    public function testAddSelfToRoute()
    {
        $collection = new CorsControllerCollection(new CorsRoute());
        $route = $collection->get('/')->getRoute();

        self::assertSame($collection, $route->getControllerCollection());
    }
}
