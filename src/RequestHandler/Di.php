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

use ConstanzeStandard\DI\Interfaces\ManagerInterface;
use ConstanzeStandard\DI\Manager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The router request handler.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class Di implements RequestHandlerInterface
{
    const DEFAULT_HANDLER_METHOD = '__invoke';

    /**
     * The single callable handler.
     * 
     * @var callable|array|object [className, methodName]
     */
    private $handler;

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
     * The DI manager.
     * 
     * @var ManagerInterface
     */
    private $manager;

    /**
     * Get the `Di` handler definition.
     * 
     * @param ContainerInterface|null $container
     * @param ManagerInterface|null $manager
     * 
     * @return \Closure
     */
    public static function getDefinition(ContainerInterface $container, ManagerInterface $manager = null)
    {
        return function($handler, array $arguments) use ($container, $manager) {
            return new static($container, $handler, $arguments, $manager);
        };
    }

    /**
     * @param ContainerInterface $container
     * @param callable $handler
     * @param array $arguments
     */
    public function __construct(ContainerInterface $container, $handler, array $arguments = [], ManagerInterface $manager = null)
    {
        $this->handler = $handler;
        $this->arguments = $arguments;
        $this->container = $container;
        $this->manager = $manager ?? new Manager($container);
    }

    /**
     * Handles a request and produces a response.
     *
     * Call the single handler to generate the response.
     * 
     * @param callable|array|object $handler
     * @param array $arguments
     * 
     * @throws \InvalidArgumentException
     * 
     * @return RequestHandlerInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (is_callable($this->handler)) {
            if (! is_string($this->handler)) {
                $this->manager->resolvePropertyAnnotation(
                    is_array($this->handler) ? $this->handler[0] : $this->handler
                );
            }
            return $this->manager->call($this->handler, $this->arguments);
        }

        if (is_string($this->handler)) {
            $targetInfo = explode('@', $this->handler);
            $instance = $this->manager->instance($targetInfo[0]);
            $this->manager->resolvePropertyAnnotation($instance);
            $method = $targetInfo[1] ?? static::DEFAULT_HANDLER_METHOD;

            return $this->manager->call([$instance, $method], $this->arguments);
        }

        throw new \InvalidArgumentException('The handler must be string or callable.');
    }
}
