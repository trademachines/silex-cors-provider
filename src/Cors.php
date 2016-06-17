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

/**
 * CORS configuration for routes.
 */
class Cors
{
    /** @var array|string[] */
    private $allowOrigins = [];

    /** @var array|string[] */
    private $allowRegexOrigins = [];

    /** @var bool */
    private $allowCredentials = false;

    /**
     * @param string $origin
     * 
     * @return $this
     */
    public function allowOrigin(string $origin)
    {
        $this->allowOrigins[] = $origin;

        return $this;
    }

    /**
     * @param string $origin
     * 
     * @return $this
     */
    public function allowRegexOrigin(string $origin)
    {
        $this->allowRegexOrigins[] = $origin;
        
        return $this;
    }

    /**
     * @param bool $allowCredentials
     *
     * @return $this
     */
    public function allowCredentials(bool $allowCredentials)
    {
        $this->allowCredentials = (bool) $allowCredentials;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowCredentials()
    {
        return $this->allowCredentials;
    }

    /**
     * @return array|string[]
     */
    public function getAllowOrigins()
    {
        return $this->allowOrigins;
    }

    /**
     * @return array|string[]
     */
    public function getAllowRegexOrigins()
    {
        return $this->allowRegexOrigins;
    }
}
