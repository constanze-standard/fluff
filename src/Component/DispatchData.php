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

use Psr\Http\Server\MiddlewareInterface;

class DispatchData
{
    const ATTRIBUTE_NAME = 'DISPATCH_DATA_ATTRIBUTE_NAME';

    /**
     * The request callback.
     * 
     * @var callable|array [className, methodName].
     */
    private $handler;

    /**
     * The middlewares of route.
     * 
     * @var MiddlewareInterface[]
     */
    private $middlewares;

    /**
     * The route url arguments.
     * 
     * @var array
     */
    private $arguments;

    /**
     * @param callable|array $handler [className, methodName].
     * @param MiddlewareInterface[]|void $middlewares
     * @param array|void $arguments
     */
    public function __construct($handler, array $middlewares = [], array $arguments = [])
    {
        $this->handler = $handler;
        $this->middlewares = $middlewares;
        $this->arguments = $arguments;
    }

    /**
     * Get the request callback.
     * 
     * @return callable|array
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Get route middlewares.
     * 
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Get route url arguments.
     * 
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
