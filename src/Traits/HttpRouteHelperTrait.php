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

namespace ConstanzeStandard\Fluff\Traits;

/**
 * The router collection helper.
 * 
 * @author Alex <blldxt@gmail.com>
 */
trait HttpRouteHelperTrait
{
    /**
     * Attach route to collector with `GET` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param array $middlewares
     * @param string|null $name
     */
    public function get($pattern, $handler, array $middlewares = [], string $name = null)
    {
        $this->withRoute('GET', $pattern, $handler, $middlewares, $name);
    }

    /**
     * Attach route to collector with `POST` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param array $middlewares
     * @param string|null $name
     */
    public function post($pattern, $handler, array $middlewares = [], string $name = null)
    {
        $this->withRoute('POST', $pattern, $handler, $middlewares, $name);
    }

    /**
     * Attach route to collector with `DELETE` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param array $middlewares
     * @param string|null $name
     */
    public function delete($pattern, $handler, array $middlewares = [], string $name = null)
    {
        $this->withRoute('DELETE', $pattern, $handler, $middlewares, $name);
    }

    /**
     * Attach route to collector with `PUT` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param array $middlewares
     * @param string|null $name
     */
    public function put($pattern, $handler, array $middlewares = [], string $name = null)
    {
        $this->withRoute('PUT', $pattern, $handler, $middlewares, $name);
    }
}
