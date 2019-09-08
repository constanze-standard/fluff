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

namespace ConstanzeStandard\Fluff\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The router request handler.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class DefaultRequestHandler extends AbstractRequestHandler
{
    /**
     * Get RequestHandler from callable.
     * 
     * @param callable|array $handler
     * @param array $params
     * 
     * @return RequestHandlerInterface
     */
    protected function getRequestHandlerFromCallable($handler, array $params): RequestHandlerInterface
    {
        if (! is_callable($handler)) {
            throw \InvalidArgumentException('The route handler must be callable.');
        }

        return new class ($handler, $params) implements RequestHandlerInterface
        {
            private $handler;
            private $params;

            public function __construct(callable $handler, array $params)
            {
                $this->handler = $handler;
                $this->params = $params;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $handler = \Closure::fromCallable($this->handler);
                return call_user_func($handler, $request, $this->params);
            }
        };
    }
}
