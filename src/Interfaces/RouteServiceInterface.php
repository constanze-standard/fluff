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

namespace ConstanzeStandard\Fluff\Interfaces;

use RuntimeException;

interface RouteServiceInterface
{
    /**
     * Add a route.
     * 
     * @param RouteInterface $route
     */
    public function addRoute(RouteInterface $route);

    /**
     * Get all routes.
     * 
     * @return RouteInterface[]
     */
    public function getRoutes(): array;

    /**
     * Get route by route name.
     * 
     * @param string $name
     * 
     * @return RouteInterface
     * 
     * @throws RuntimeException
     */
    public function getRouteByName(string $name): RouteInterface;

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
    public function getUrlByRoute(RouteInterface $route, array $arguments = [], array $queryParams = []): string;

    /**
     * Get url by route name.
     * 
     * @param string $name
     * @param array $arguments
     * @param array $queryParams
     * 
     * @return string
     */
    public function urlFor(string $name, array $arguments = [], array $queryParams = []): string;
}
