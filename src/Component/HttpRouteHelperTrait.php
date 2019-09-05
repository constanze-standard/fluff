<?php
/**
 * Copyright 2019 Speed Sonic <blldxt@gmail.com>
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

trait HttpRouteHelperTrait
{
    /**
     * Attach route to collector with `GET` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     */
    public function get($pattern, $controller, array $data = [])
    {
        $this->withRoute('GET', $pattern, $controller, $data);
    }

    /**
     * Attach route to collector with `POST` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     */
    public function post($pattern, $controller, array $data = [])
    {
        $this->withRoute('POST', $pattern, $controller, $data);
    }

    /**
     * Attach route to collector with `DELETE` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     */
    public function delete($pattern, $controller, array $data = [])
    {
        $this->withRoute('DELETE', $pattern, $controller, $data);
    }

    /**
     * Attach route to collector with `PUT` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     */
    public function put($pattern, $controller, array $data = [])
    {
        $this->withRoute('PUT', $pattern, $controller, $data);
    }
}
