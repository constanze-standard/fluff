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

use ConstanzeStandard\Fluff\Interfaces\RouteInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * The http route.
 * 
 * @author Alex <omytty@126.com>
 */
class Route implements RouteInterface
{
    /**
     * One or more http methods
     * 
     * @var array|string
     */
    private string|array $httpMethods;

    /**
     * Url pattern.
     * 
     * @var string
     */
    private string $pattern;

    /**
     * Callable message.
     * 
     * @var mixed
     */
    private mixed $handler;

    /**
     * Route middlewares.
     * 
     * @var MiddlewareInterface[]
     */
    private array $middlewares = [];

    /**
     * The route name.
     * 
     * @var string|null
     */
    private ?string $name;

    /**
     * @param array|string $methods
     * @param string $pattern
     * @param mixed $handler
     * @param MiddlewareInterface[] $middlewares
     * @param string|null $name
     */
    public function __construct(array|string $methods, string $pattern, mixed $handler, array $middlewares = [], ?string $name = null)
    {
        $this->httpMethods = $methods;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->name = $name;

        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }
    }

    /**
     * Push an route middleware
     * 
     * @param MiddlewareInterface $middleware
     * 
     * @return RouteInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): RouteInterface
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Set route name.
     * 
     * @param string $name
     * 
     * @return RouteInterface
     */
    public function setName(string $name): RouteInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the route middlewares.
     * 
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Get the route name.
     * 
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the route handler.
     * 
     * @return mixed
     */
    public function getHandler(): mixed
    {
        return $this->handler;
    }

    /**
     * Get the route pattern.
     * 
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Get the route http methods.
     * 
     * @return array|string
     */
    public function getHttpMethods(): array|string
    {
        return $this->httpMethods;
    }
}
