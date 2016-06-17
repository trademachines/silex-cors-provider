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

use Silex\Application;
use Silex\ControllerCollection;
use Silex\Route;

/**
 * Specific route implementation for addings cors functionality per route.
 */
class CorsRoute extends Route
{
    /** @var ControllerCollection */
    private $controllerCollection;

    /** @var Cors */
    private $cors;

    /** @var CorsRoute */
    public $realRoute;

    /**
     * @return ControllerCollection
     */
    public function getControllerCollection()
    {
        return $this->controllerCollection;
    }

    /**
     * @param ControllerCollection $controllerCollection
     */
    public function setControllerCollection(ControllerCollection $controllerCollection)
    {
        $this->controllerCollection = $controllerCollection;
    }

    /**
     * @param Cors $cors
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function cors(Cors $cors)
    {
        if (!$this->controllerCollection) {
            throw new \RuntimeException(
                'A controller collection needs to be set, did you register the service provider properly?'
            );
        }

        /** @var CorsRoute $preflightRoute */
        $preflightRoute = $this->controllerCollection->options(
            $this->getPath(),
            'cors:handlePreflight'
        )->getRoute();
        $preflightRoute->setDefaults($this->getDefaults());
        $preflightRoute->setRequirements($this->getRequirements());

        if (!$preflightRoute instanceof CorsRoute) {
            throw new \RuntimeException(
                'The route returned from controller collection does not have the proper type, did you register the service provider properly?'
            );
        }

        $preflightRoute->realRoute = $this;
        $this->cors           = $cors;
        $this->after('cors:handleSimple');

        return $this;
    }

    /**
     * @return Cors
     */
    public function getCors()
    {
        return $this->cors;
    }

    /**
     * @return CorsRoute
     */
    public function getRealRoute()
    {
        return $this->realRoute;
    }
}
