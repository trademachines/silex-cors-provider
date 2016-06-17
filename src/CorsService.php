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

use Psr\Log\LoggerInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;

/**
 * Service taking care of adding CORS header when necessary and allowed.
 */
class CorsService
{
    /** @var RouteCollection */
    private $routes;

    /**
     * CorsService constructor.
     *
     * @param RouteCollection $routes
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param Request $request
     * 
     * @return Response
     */
    public function handlePreflight(Request $request)
    {
        $response = Response::create('', Response::HTTP_NO_CONTENT);

        if ($this->isPreflightRequest($request)) {
            $this->doHandle(
                $request,
                $response,
                function (Response $response, CorsRoute $route) {
                    $response->headers->set('Access-Control-Allow-Methods', implode(', ', $route->getMethods()));
                }
            );
        }

        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function handleSimple(Request $request, Response $response)
    {
        if ($this->isSimpleRequest($request)) {
            $this->doHandle($request, $response);
        }
    }

    private function doHandle(Request $request, Response $response, callable $additional = null)
    {
        $info = $this->getInformation($request);

        if (!$info) {
            return;
        }

        /** @var Cors $cors */
        /** @var CorsRoute $realRoute */
        list($realRoute, $cors) = $info;

        if (!$this->isCorsAllowed($request, $cors)) {
            return;
        }

        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));
        if ($cors->isAllowCredentials()) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($additional) {
            $additional($response, $realRoute, $cors);
        }
    }

    private function isSimpleRequest(Request $request)
    {
        return $request->headers->has('Origin');
    }

    private function isPreflightRequest(Request $request)
    {
        return 'OPTIONS' === $request->getMethod() && $request->headers->has('Access-Control-Request-Method');
    }

    private function getInformation(Request $request)
    {
        /** @var CorsRoute $route */
        $name = $request->attributes->get('_route');

        if (null === $route = $this->routes->get($name)) {
            return false;
        }

        $realRoute = $route->getRealRoute();

        // OPTIONS request
        if ($realRoute) {
            return [$realRoute, $realRoute->getCors()];
        } else {
            return [$route, $route->getCors()];
        }
    }

    private function isCorsAllowed(Request $request, Cors $cors)
    {
        $origin = $request->headers->get('Origin');

        foreach ($cors->getAllowOrigins() as $tOrigin) {
            if ($origin === $tOrigin) {
                return true;
            }
        }

        foreach ($cors->getAllowRegexOrigins() as $tOrigin) {
            if (1 === preg_match($tOrigin, $origin)) {
                return true;
            }
        }

        return false;
    }
}
