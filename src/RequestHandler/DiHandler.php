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
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The router request handler.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class DiHandler implements RequestHandlerInterface
{
    const DEFAULT_HANDLER_METHOD = '__invoke';

    /**
     * The single callable handler.
     * 
     * @var callable|array [className, methodName]
     */
    private $routeHandler;

    /**
     * The route url arguments.
     * 
     * @var array
     */
    private $arguments;

    /**
     * The PSR-11 container.
     * 
     * @var ContainerInterface
     */
    private $container;

    /**
     * Get the `DiHandler` definition.
     * 
     * @param ContainerInterface|null $container
     * 
     * @return \Closure
     */
    public static function getDefinition(ContainerInterface $container)
    {
        return function($handler, $arguments) use ($container) {
            return new static($container, $handler, $arguments);
        };
    }

    /**
     * @param ContainerInterface $container
     * @param callable $routeHandler
     * @param array $arguments
     */
    public function __construct(ContainerInterface $container, $routeHandler, array $arguments = [])
    {
        $this->routeHandler = $routeHandler;
        $this->arguments = $arguments;
        $this->container = $container;
    }

    /**
     * Handles a request and produces a response.
     *
     * Call the single handler to generate the response.
     * 
     * @param callable|array $handler
     * @param array $arguments
     * 
     * @throws \InvalidArgumentException
     * 
     * @return RequestHandlerInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $invoker = new Invoker($this->container);

        switch (true) {
            case is_callable($this->routeHandler):
                return $invoker->call($this->routeHandler, $this->arguments);
            case is_string($this->routeHandler):
                $callback = explode('@', $this->routeHandler);
                return $invoker->callMethod(
                    $invoker->new($callback[0]),
                    $callback[1] ?? static::DEFAULT_HANDLER_METHOD,
                    $this->arguments
                );
        }
        throw new \InvalidArgumentException('Route handler must be string or callable.');
    }
}
