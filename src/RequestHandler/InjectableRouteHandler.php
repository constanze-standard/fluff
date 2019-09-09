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

use Beige\Invoker\Interfaces\InvokerInterface;
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
class InjectableRouteHandler extends AbstractRouteHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * The injectable invoker.
     * 
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, string $attributeName = 'route')
    {
        parent::__construct($attributeName);
        $this->container = $container;
    }

    /**
     * Get the invoker.
     * 
     * @return InvokerInterface
     */
    public function getInvoker(): InvokerInterface
    {
        if (!$this->invoker) {
            $this->invoker = new Invoker($this->container);
        }
        return $this->invoker;
    }

    /**
     * Get RequestHandler from callable.
     * 
     * @param callable|array $handler
     * @param array $params
     * 
     * @return RequestHandlerInterface
     */
    protected function getRequestHandler($handler, array $params): RequestHandlerInterface
    {
        $invoker = $this->getInvoker();
        return new class ($this->container, $handler, $params, $invoker) implements RequestHandlerInterface
        {
            private $container;
            private $handler;
            private $params;
            private $invoker;

            public function __construct(ContainerInterface $container, $handler, array $params, InvokerInterface $invoker)
            {
                $this->container = $container;
                $this->handler = $handler;
                $this->params = $params;
                $this->invoker = $invoker;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                if (is_array($this->handler)) {
                    $instance = $this->invoker->new($this->handler[0]);
                    return $this->invoker->callMethod(
                        $instance,
                        $this->handler[1] ?? '__invoke',
                        $this->params
                    );
                }
        
                if (is_callable($this->handler)) {
                    return $this->invoker->call($this->handler, $this->params);
                }

                throw new \InvalidArgumentException('Route handler must be array or callable.');
            }
        };
    }
}
