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

namespace ConstanzeStandard\Fluff\Component;

use ConstanzeStandard\Fluff\Interfaces\RouteInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteServiceInterface;
use RuntimeException;

/**
 * Routes collect and parse.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class RouteService implements RouteServiceInterface
{
    /**
     * Routes
     * 
     * @var RouteInterface[]
     */
    private $routes;

    /**
     * Set routes.
     * 
     * @param Route[] $routes
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
    }

    /**
     * Add a route.
     * 
     * @param RouteInterface $route
     */
    public function addRoute(RouteInterface $route)
    {
        $this->routes[] = $route;
    }

    /**
     * Get all routes.
     * 
     * @return RouteInterface[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get route by route name.
     * 
     * @param string $name
     * 
     * @return RouteInterface
     * 
     * @throws RuntimeException
     */
    public function getRouteByName(string $name): RouteInterface
    {
        foreach ($this->routes as $route) {
            if ($name and $route->getName() === $name) {
                return $route;
            }
        }

        throw new RuntimeException('Can not find the route with name `'.$name.'`.');
    }

    /**
     * Get url by route.
     * 
     * @param RouteInterface $route
     * @param array $arguments
     * @param array $queryParams
     * 
     * @return string
     * 
     * @throws RuntimeException
     */
    public function getUrlByRoute(RouteInterface $route, array $arguments = [], array $queryParams = []): string
    {
        $url = $route->getPattern();

        foreach ($arguments as $name => $argument) {
            if (preg_match("/{{$name}:(?=.*)(.*)}/", $url, $matches)) {
                if (!preg_match("/^{$matches[1]}$/", $argument)) {
                    throw new RuntimeException('The URL argument ' . $name . ' format mismatch.');
                }
            }
            $url = preg_replace("/{{$name}(:.*)?}/", $argument, $url);
        }

        if ($queryParams) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $_SERVER['SCRIPT_NAME'] . $url;
    }

    /**
     * Get url by route name.
     * 
     * @param string $name
     * @param array $arguments
     * @param array $queryParams
     * 
     * @return string
     */
    public function urlFor(string $name, array $arguments = [], array $queryParams = []): string
    {
        $route = $this->getRouteByName($name);
        return $this->getUrlByRoute($route, $arguments, $queryParams);
    }
}
