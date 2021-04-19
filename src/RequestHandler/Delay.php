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
class Delay implements RequestHandlerInterface
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
     * The unit handler definition.
     * 
     * @var callable
     */
    private $definition;

    /**
     * The init strategy.
     * 
     * @var callable
     */
    private $strategy;

    /**
     * Get the `Delay` handler definition.
     * 
     * @param callable $strategy
     * @param callable $definition
     * 
     * @return \Closure
     */
    public static function getDefinition(callable $strategy, callable $definition): \Closure
    {
        return function($handler, array $arguments) use ($strategy, $definition) {
            return new static($strategy, $definition, $handler, $arguments);
        };
    }

    /**
     * Parse handler with initial arguments.
     *
     * @param callable|string|array $handler
     * @param callable $strategy
     *
     * @return callable|string|array
     */
    private static function handlerToCallable(callable|string|array $handler, callable $strategy): callable|string|array
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler)) {
            $callback = explode('@', $handler);
            return $strategy(
                $callback[0],
                $callback[1] ?? static::DEFAULT_HANDLER_METHOD
            );
        }

        throw new \InvalidArgumentException('Route handler must be string or callable.');
    }

    /**
     * @param callable $strategy
     * @param callable $definition
     * @param callable|string|array $handler Callable object or class name.
     * @param array $arguments
     */
    public function __construct(
        callable $strategy,
        callable $definition,
        callable|string|array $handler,
        array $arguments = []
    )
    {
        $this->strategy = $strategy;
        $this->definition = $definition;
        $this->handler = $handler;
        $this->arguments = $arguments;
    }

    /**
     * Handles a request and produces a response.
     *
     * Call the single handler to generate the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = static::handlerToCallable($this->handler, $this->strategy);
        return call_user_func($this->definition, $handler, $this->arguments)->handle($request);
    }
}
