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

use Psr\Http\Server\MiddlewareInterface;

interface RouteInterface
{
    /**
     * Push an route middleware
     * 
     * @param MiddlewareInterface $middleware
     * 
     * @return RouteInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): RouteInterface;

    /**
     * Get the route middlewares.
     * 
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array;

    /**
     * Get route name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set route name.
     * 
     * @param string $name
     * 
     * @return RouteInterface
     */
    public function setName(string $name): RouteInterface;

    /**
     * Get the route handler.
     * 
     * @return mixed
     */
    public function getHandler(): mixed;

    /**
     * Get the route pattern.
     * 
     * @return string
     */
    public function getPattern(): string;

    /**
     * Get the route http methods.
     * 
     * @return array|string
     */
    public function getHttpMethods(): array|string;
}
