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
class DelayHandler implements RequestHandlerInterface
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
     * Get the `Delay` handler definition.
     * 
     * @param mixed[] $initialArguments
     * 
     * @return \Closure
     */
    public static function getDefinition(...$initialArguments)
    {
        return function($handler, array $arguments) use ($initialArguments) {
            return new static($handler, $arguments, ...$initialArguments);
        };
    }

    /**
     * @param callable|string $handler Callable object or class name.
     * @param array $arguments
     */
    public function __construct($handler, array $arguments = [], ...$initialArguments)
    {
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
        if (is_callable($this->handler)) {
            return (new Handler($this->handler, $this->arguments))->handle($request);
        }

        if (is_string($this->handler)) {
            $callback = explode('@', $this->handler);
            $className = $callback[0];
            $handler = [
                new $className(...$this->initialArguments),
                $callback[1] ?? static::DEFAULT_HANDLER_METHOD
            ];
            return (new Handler($handler, $this->arguments))->handle($request);
        }

        throw new \InvalidArgumentException('Route handler must be string or callable.');
    }
}
