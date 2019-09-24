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

use RuntimeException;

/**
 * Routes collect and parse.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class RouteService
{
    /**
     * Routes
     * 
     * @var Route[]
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
     * @param Route $route
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * Get all routes.
     * 
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get route by route name.
     * 
     * @param string $name
     * 
     * @return Route
     * 
     * @throws RuntimeException
     */
    public function getRouteByName(string $name): Route
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
     * @param Route $route
     * @param array $variables
     * @param array $queryParams
     * 
     * @return string
     */
    public function getUrlByRoute(Route $route, array $variables = [], array $queryParams = []): string
    {
        $url = $route->getPattern();

        foreach ($variables as $name => $variable) {
            $url = preg_replace("/{{$name}(:.*)?}/", $variable, $url);
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
     * @param array $variables
     * @param array $queryParams
     * 
     * @return string
     */
    public function urlFor(string $name, array $variables = [], array $queryParams = []): string
    {
        $route = $this->getRouteByName($name);
        return $this->getUrlByRoute($route, $variables, $queryParams);
    }
}
