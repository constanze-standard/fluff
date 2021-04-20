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

namespace ConstanzeStandard\Fluff\Interfaces;

interface RouteHelperInterface
{
    /**
     * Attach route to collector with `GET` method.
     * 
     * @param string $pattern
     * @param array|string|\Closure $handler
     * @param array $middlewares
     * @param string|null $name
     * 
     * @return RouteInterface
     */
    public function get(string $pattern, array|string|\Closure $handler, array $middlewares = [], string $name = null): RouteInterface;

    /**
     * Attach route to collector with `POST` method.
     * 
     * @param string $pattern
     * @param array|string|\Closure $handler
     * @param array $middlewares
     * @param string|null $name
     * 
     * @return RouteInterface
     */
    public function post(string $pattern, array|string|\Closure $handler, array $middlewares = [], string $name = null): RouteInterface;

    /**
     * Attach route to collector with `DELETE` method.
     * 
     * @param string $pattern
     * @param array|string|\Closure $handler
     * @param array $middlewares
     * @param string|null $name
     * 
     * @return RouteInterface
     */
    public function delete(string $pattern, array|string|\Closure $handler, array $middlewares = [], string $name = null): RouteInterface;

    /**
     * Attach route to collector with `PUT` method.
     * 
     * @param string $pattern
     * @param array|string|\Closure $handler
     * @param array $middlewares
     * @param string|null $name
     * 
     * @return RouteInterface
     */
    public function put(string $pattern, array|string|\Closure $handler, array $middlewares = [], string $name = null): RouteInterface;

    /**
     * Attach route to collector with `OPTIONS` method.
     * 
     * @param string $pattern
     * @param array|string|\Closure $handler
     * @param array $middlewares
     * @param string|null $name
     * 
     * @return RouteInterface
     */
    public function options(string $pattern, array|string|\Closure $handler, array $middlewares = [], string $name = null): RouteInterface;
}
