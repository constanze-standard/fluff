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
     * @var callable
     */
    private $handler;

    /**
     * The route url arguments.
     * 
     * @var array
     */
    private array $arguments;

    /**
     * The DI manager.
     * 
     * @var ManagerInterface
     */
    private ManagerInterface $manager;

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
        return function(callable $handler, array $arguments) use ($container, $manager) {
            return new static($container, $handler, $arguments, $manager);
        };
    }

    /**
     * @param ContainerInterface $container
     * @param callable $handler
     * @param array $arguments
     * @param ManagerInterface|null $manager
     */
    public function __construct(ContainerInterface $container, callable $handler, array $arguments = [], ManagerInterface $manager = null)
    {
        $this->handler = $handler;
        $this->arguments = $arguments;
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
        return $this->manager->call($this->handler, $this->arguments);
    }
}
