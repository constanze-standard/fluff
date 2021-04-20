<?php

/**
 * Copyright 2019 Alex <omytty@126.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ConstanzeStandard\Fluff\Routing;

use ConstanzeStandard\Fluff\Interfaces\RouteGroupInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteInterface;
use Psr\Http\Server\MiddlewareInterface;

class RouteGroupProxy
{
    /**
     * The root route group.
     * 
     * @var RouteGroupInterface
     */
    protected RouteGroupInterface $routeGroup;

    /**
     * @param RouteGroupInterface $routeGroup
     */
    public function __construct(RouteGroupInterface $routeGroup)
    {
        $this->routeGroup = $routeGroup;
    }

    /**
     * Add a router middleware.
     * 
     * @param MiddlewareInterface $middleware
     * 
     * @return MiddlewareInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareInterface
    {
        return $this->routeGroup->addMiddleware($middleware);
    }

    /**
     * Add a route to collection.
     * 
     * @param RouteInterface $route
     * 
     * @return RouteInterface
     */
    public function addRoute(RouteInterface $route): RouteInterface
    {
        return $this->routeGroup->addRoute($route);
    }

    /**
     * Register route data to collection.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param array|string|\Closure $handler
     * @param MiddlewareInterface[] $middlewares
     * @param string|null $name
     * 
     * @return RouteInterface
     */
    public function add(array|string $methods, string $pattern, array|string|\Closure $handler, array $middlewares = [], string $name = null): RouteInterface
    {
        return $this->routeGroup->add($methods, $pattern, $handler, $middlewares, $name);
    }

    /**
     * Create a route group.
     * 
     * @param string $prefix
     * @param MiddlewareInterface[] $middlewares
     * 
     * @return RouteGroupInterface
     */
    public function deriveGroup(string $prefix = '', array $middlewares = []): RouteGroupInterface
    {
        return $this->routeGroup->derive($prefix, $middlewares);
    }

    /**
     * Set group prefix.
     * 
     * @param string $prefix
     * 
     * @return static
     */
    public function setPrefix(string $prefix): static
    {
        $this->routeGroup->setPrefix($prefix);
        return $this;
    }

    /**
     * Get the root route group.
     * 
     * @return RouteGroupInterface
     */
    public function getRootGroup(): RouteGroupInterface
    {
        return $this->routeGroup;
    }
}
