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

use Beige\Invoker\Invoker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

/**
 * The router request handler.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class InjectableRequestHandler extends AbstractRequestHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $requestPreparedHandler;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Register a handler on request preqared.
     * 
     * @param callable $handler A callable object with two parameters:
     *  - `\Psr\Http\Message\ServerRequestInterface`.
     *  - `\Psr\Container\ContainerInterface`
     */
    public function onRequestPrepared(callable $handler)
    {
        $this->requestPreparedHandler = $handler;
    }

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
        return new class ($this->container, $handler, $params, $this->requestPreparedHandler) implements RequestHandlerInterface
        {
            private $container;
            private $handler;
            private $params;
            private $requestPreparedHandler;

            public function __construct(ContainerInterface $container, $handler, array $params, $requestPreparedHandler)
            {
                $this->container = $container;
                $this->handler = $handler;
                $this->params = $params;
                $this->requestPreparedHandler = $requestPreparedHandler;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $invoker = new Invoker($this->container);
                if ($this->requestPreparedHandler) {
                    call_user_func($this->requestPreparedHandler, $request, $this->container);
                }

                if (is_array($this->handler)) {
                    $instance = $invoker->new($this->handler[0]);
                    return $invoker->callMethod(
                        $instance,
                        $this->handler[1] ?? 'index',
                        $this->params
                    );
                }
        
                if (is_callable($this->handler)) {
                    return $invoker->call($this->handler, $this->params);
                }

                throw new \InvalidArgumentException('Route handler must be array or callable.');
            }
        };
    }
}
