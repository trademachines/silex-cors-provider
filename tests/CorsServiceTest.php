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

use Silex\Application;
use Silex\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Trademachines\Silex\Provider\Cors\Cors;
use Trademachines\Silex\Provider\Cors\CorsControllerCollection;
use Trademachines\Silex\Provider\Cors\CorsRoute;
use Trademachines\Silex\Provider\Cors\CorsService;

class CorsServiceTest extends \PHPUnit_Framework_TestCase
{
    const REAL_ROUTE_NAME          = 'test';
    const PREFLIGHT_REQUEST_METHOD = 'OPTIONS';
    const PREFLIGHT_ROUTE          = 'route.preflight';
    const REAL_ROUTE               = 'route.real';

    public function testPreflightReturnsEmptyResponse()
    {
        $service  = new CorsService(new RouteCollection());
        $response = $service->handlePreflight(Request::create('/'));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    public function testDontAllowCorsForWrongOriginOnPreflight()
    {
        $cors = (new Cors())->allowOrigin('http://sub.domain.com');
        list($routes, $request) = $this->getPreflightRequestInfo($cors, ['Origin' => 'http://wrong.domain.com']);

        $service  = new CorsService($routes);
        $response = $service->handlePreflight($request);

        self::assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }

    public function testDontAllowCorsForWrongOriginOnSimpleRequest()
    {
        $cors = (new Cors())->allowOrigin('http://sub.domain.com');
        list($routes, $request) = $this->getSimpleRequestInfo($cors, ['Origin' => 'http://wrong.domain.com']);

        $service  = new CorsService($routes);
        $response = $service->handlePreflight($request);

        self::assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }

    /**
     * @dataProvider provideCorsWithOrigin
     */
    public function testSetMandatoryHeadersIfPreflightIsOk(Cors $cors)
    {
        $origin = 'http://sub.domain.com';
        $cors->allowHeaders(['accept']);
        list($routes, $request) = $this->getPreflightRequestInfo($cors, ['Origin' => $origin]);

        $service  = new CorsService($routes);
        $response = $service->handlePreflight($request);

        self::assertEquals($origin, $response->headers->get('Access-Control-Allow-Origin'));
        self::assertEquals('PUT', $response->headers->get('Access-Control-Allow-Methods'));
        self::assertEquals('accept', $response->headers->get('Access-Control-Allow-Headers'));
    }

    /**
     * @dataProvider provideCorsWithOrigin
     */
    public function testSetMandatoryHeadersIfSimpleRequestIsOk($cors)
    {
        $origin = 'http://sub.domain.com';
        list($routes, $request) = $this->getSimpleRequestInfo($cors, ['Origin' => $origin]);

        $response = Response::create();
        $service  = new CorsService($routes);
        $service->handleSimple($request, $response);

        self::assertEquals($origin, $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testAddCredentialsHeaderIfSettingIsTrue()
    {
        $cors = (new Cors())->allowOrigin('http://sub.domain.com')->allowCredentials(true);
        list($routes, $request) = $this->getPreflightRequestInfo($cors, ['Origin' => 'http://sub.domain.com']);

        $service  = new CorsService($routes);
        $response = $service->handlePreflight($request);

        self::assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
    }

    public function provideCorsWithOrigin()
    {
        return [
            'w/o regex' => [(new Cors())->allowOrigin('http://sub.domain.com')],
            'w/ regex'  => [(new Cors())->allowRegexOrigin('#^http://...\.domain\.com$#')],
        ];
    }

    private function getPreflightRequestInfo(Cors $cors, array $headers = [])
    {
        $headers['Access-Control-Request-Method'] = self::PREFLIGHT_REQUEST_METHOD;
        return $this->getRequestInfo('PUT', $cors, $headers, self::PREFLIGHT_ROUTE);
    }

    private function getSimpleRequestInfo(Cors $cors, array $headers = [])
    {
        return $this->getRequestInfo('GET', $cors, $headers, self::REAL_ROUTE);
    }

    private function getRequestInfo($realRouteMethod, Cors $cors, array $headers, $use = self::PREFLIGHT_ROUTE)
    {
        $controllerCollection = new CorsControllerCollection(new CorsRoute());
        $controllerCollection->match('/test')->method($realRouteMethod)->bind(self::REAL_ROUTE_NAME)->cors($cors);

        $routes = $controllerCollection->flush();

        $allRoutes = $routes->all();
        unset($allRoutes['test']);
        list($preflightRouteName,) = each($allRoutes);

        $request = Request::create('/test');
        $request->headers->add($headers);

        switch ($use) {
            case self::PREFLIGHT_ROUTE:
                $request->setMethod(self::PREFLIGHT_REQUEST_METHOD);
                $request->attributes->set('_route', $preflightRouteName);
                break;
            case self::REAL_ROUTE:
                $request->setMethod($realRouteMethod);
                $request->attributes->set('_route', self::REAL_ROUTE_NAME);
                break;
        }

        return [$routes, $request];
    }
}
