<?php

/**
 * Copyright 2019 Alex <blldxt@gmail.com>
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
use ConstanzeStandard\Fluff\Traits\HttpRouteHelperTrait;
use Psr\Http\Server\MiddlewareInterface;

/**
 * The http route.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class RouteGroup implements RouteGroupInterface
{
    use HttpRouteHelperTrait;

    /**
     * The url prefix.
     * 
     * @var string
     */
    private string $prefix;

    /**
     * Middlewares of current group.
     * 
     * @var MiddlewareInterface[]
     */
    private array $middlewares = [];

    /**
     * Routes of currnt group.
     * 
     * @var Route[]
     */
    private array $routes = [];

    /**
     * @param string $prefix
     * @param array $middlewares
     */
    public function __construct(string $prefix = '', array $middlewares = [])
    {
        $this->prefix = $prefix;
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }
    }

    /**
     * Register route data to collection.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param MiddlewareInterface[] $middlewares
     * @param string|null $name
     * 
     * @return RouteInterface
     */
    public function add($methods, string $pattern, $handler, array $middlewares = [], string $name = null): RouteInterface
    {
        $pattern = $this->prefix . $pattern;
        $middlewares = array_merge($this->middlewares, $middlewares);

        $route = new Route($methods, $pattern, $handler, $middlewares, $name);
        return $this->addRoute($route);
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
        $this->routes[] = $route;
        return $route;
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
        $this->middlewares[] = $middleware;
        return $middleware;
    }

    /**
     * Set group prefix.
     * 
     * @param string $prefix
     * 
     * @return RouteGroupInterface
     */
    public function setPrefix(string $prefix): RouteGroupInterface
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Get all routes.
     * 
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Derived a group from current group.
     * 
     * @param string $prefix
     * @param MiddlewareInterface[] $middlewares
     * 
     * @return RouteGroupInterface
     */
    public function derive(string $prefix = '', array $middlewares = []): RouteGroupInterface
    {
        return new static(
            $this->prefix . $prefix,
            array_merge($this->middlewares, $middlewares)
        );
    }
}
