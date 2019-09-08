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

namespace ConstanzeStandard\Fluff;

use ConstanzeStandard\Fluff\Component\HttpRouteHelperTrait;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Fluff\RequestHandler\DefaultRequestHandler;
use ConstanzeStandard\RequestHandler\Dispatcher as RequestDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements RequestHandlerInterface
{
    use HttpRouteHelperTrait;

    /**
     * The request disptcher.
     * 
     * @var RequestDispatcher
     */
    private $requestDispatcher;

    /**
     * @var RouterMiddleware
     */
    private $routerMiddleware;

    /**
     * Application constructor.
     * set the custom container.
     *
     * @param ContainerInterface $container
     */
    public function __construct(RequestHandlerInterface $requestHandler = null)
    {
        $requestHandler = $requestHandler ?? new DefaultRequestHandler();
        $this->requestDispatcher = new RequestDispatcher($requestHandler);
        $this->routerMiddleware = new RouterMiddleware();
        $this->addMiddleware($this->routerMiddleware);
    }

    /**
     * Get router middleware.
     * 
     * @return RouterMiddleware
     */
    public function getRouterMiddleware()
    {
        return $this->routerMiddleware;
    }

    /**
     * Add a middleware.
     * 
     * @param MiddlewareInterface $middleware
     * 
     * @return MiddlewareInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->requestDispatcher->addMiddleware($middleware);
        return $middleware;
    }

    /**
     * Dispatch the request to middlewares.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->requestDispatcher->handle($request);

        if (strcasecmp($request->getMethod(), 'HEAD') === 0) {
            $body = $request->getBody();
            if ($body->isWritable() && $body->isSeekable()) {
                $body->rewind();
                $body->write('');
            }
        }
        return $response;
    }

    /**
     * Attach data to collection.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param callable|array $handler
     * @param array $middlewares
     * @param string|null $name
     * 
     * @throws \InvalidArgumentException
     */
    public function withRoute($methods, string $pattern, $handler, array $middlewares = [], string $name = null)
    {
        $this->routerMiddleware->withRoute($methods, $pattern, $handler, $middlewares, $name);
    }

    /**
     * Create a route group.
     * 
     * @param string $prefixPattern
     * @param array $middlewares
     * @param callable $callback
     */
    public function withGroup(string $prefixPattern, array $middlewares = [], callable $callback)
    {
        $this->routerMiddleware->withGroup($prefixPattern, $middlewares, $callback);
    }
}
