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
use InvalidArgumentException;
use ConstanzeStandard\Fluff\Interfaces\RouteParserInterface;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;

/**
 * Router parser for collection.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class RouteParser implements RouteParserInterface
{
    /**
     * The route collection.
     * 
     * @var CollectionInterface
     */
    private $routeCollection;

    /**
     * Base path.
     * 
     * @var string
     */
    private $basePath = '';

    /**
     * Construct route service.
     * 
     * @param CollectionInterface $routeCollection
     */
    public function __construct(CollectionInterface $routeCollection, $basePath = '')
    {
        $this->routeCollection = $routeCollection;
        $this->basePath = $basePath;
    }

    /**
     * Set the host name
     * 
     * @param string $basePath
     */
    public function setHost(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Get the relative url by route name.
     * 
     * @param string $name Name of route.
     * @param array $params Parameters of url.
     * @param array $queryParams The query parameters.
     * 
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * 
     * @return string The URL.
     */
    public function getRelativeUrlByName(string $name, array $params = [], array $queryParams = []): string
    {
        return $this->getRelativeUrlByAttributes(
            ['name' => $name],
            $params,
            $queryParams
        );
    }

    /**
     * Get the full url by route.
     * 
     * @param string $name Name of route.
     * @param array $params Parameters of url.
     * @param array $queryParams The query parameters.
     * 
     * @return string The URL.
     */
    public function getUrlByName(string $name, array $params = [], array $queryParams = []): string
    {
        $url = $this->getRelativeUrlByName($name, $params, $queryParams);
        return $this->basePath . $url;
    }

    /**
     * Get the relative url by attribute.
     * 
     * @param array $attrs attrs of route.
     * @param array $params Parameters of url.
     * @param array $queryParams The query parameters.
     * 
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * 
     * @return string The URL.
     */
    private function getRelativeUrlByAttributes(array $attrs, array $params = [], array $queryParams = []): string
    {
        $route = $this->routeCollection->getRoutesByData($attrs, true);
        if ($route) {
            list($url, $_, $_, $variables) = $route;
            if ($variables) {
                foreach ($variables as $variable) {
                    if (!isset($params[$variable])) {
                        throw new InvalidArgumentException('Missing data for URL parameter: ' . $variable);
                    }
                    $url = str_replace("{{$variable}}", $params[$variable], $url);
                }
            }
            if ($queryParams) {
                $url .= '?' . http_build_query($queryParams);
            }
            return $url;
        }
        throw new RuntimeException('Route does not exist with attributes');
    }
}
