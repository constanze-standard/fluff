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
    private $arguments;

    /**
     * The initial arguments for handler.
     * 
     * @var array
     */
    private $initialArguments;

    /**
     * The unit handler definition.
     * 
     * @var callable
     */
    private $definition;

    /**
     * Get the `Delay` handler definition.
     * 
     * @param callable $definition
     * @param mixed[] $initialArguments
     * 
     * @return \Closure
     */
    public static function getDefinition(callable $definition, ...$initialArguments)
    {
        return function($handler, array $arguments) use ($definition, $initialArguments) {
            return new static($definition, $handler, $arguments, ...$initialArguments);
        };
    }

    /**
     * Parse handler with initial arguments.
     * 
     * @param callable|string $handler
     * @param array $initialArguments
     * 
     * @return callable
     */
    private static function handlerToCallable($handler, array $initialArguments)
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler)) {
            $callback = explode('@', $handler);
            return [
                new $callback[0](...$initialArguments),
                $callback[1] ?? static::DEFAULT_HANDLER_METHOD
            ];
        }

        throw new \InvalidArgumentException('Route handler must be string or callable.');
    }

    /**
     * @param callable $definition
     * @param callable|string $handler Callable object or class name.
     * @param array $arguments
     */
    public function __construct(callable $definition, $handler, array $arguments = [], ...$initialArguments)
    {
        $this->definition = $definition;
        $this->handler = $handler;
        $this->arguments = $arguments;
        $this->initialArguments = $initialArguments;
    }

    /**
     * Handles a request and produces a response.
     *
     * Call the single handler to generate the response.
     * 
     * @param callable|array $handler
     * @param array $params
     * 
     * @return RequestHandlerInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = static::handlerToCallable($this->handler, $this->initialArguments);
        return call_user_func($this->definition, $handler, $this->arguments)->handle($request);
    }
}
