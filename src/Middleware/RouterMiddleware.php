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

namespace ConstanzeStandard\Fluff\Middleware;

use ConstanzeStandard\Fluff\Component\DispatchInformation;
use ConstanzeStandard\Fluff\Component\Route;
use ConstanzeStandard\Fluff\Component\RouteParser;
use ConstanzeStandard\Fluff\Exception\HttpMethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\HttpNotFoundException;
use ConstanzeStandard\Fluff\Interfaces\RouteableInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteParserInterface;
use ConstanzeStandard\Fluff\Traits\HttpRouteHelperTrait;
use ConstanzeStandard\Route\Collector;
use ConstanzeStandard\Route\Dispatcher;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use ConstanzeStandard\Route\Interfaces\DispatcherInterface;
use ConstanzeStandard\Standard\Http\Server\DispatchInformationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Router middleware.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class RouterMiddleware implements MiddlewareInterface, RouteableInterface
{
    use HttpRouteHelperTrait;

    /**
     * The dispatch data flag.
     * 
     * @var string
     */
    private $dispathDataFlag;

    /**
     * The route collection.
     * 
     * @var CollectionInterface
     */
    private $collection;

    /**
     * The route dispatcher.
     * 
     * @var DispatcherInterface
     */
    private $dispatcher;

    /**
     * Previous pattern prefix
     * 
     * @var string
     */
    private $privPrefix = '';

    /**
     * Previous middlewares.
     * 
     * @var MiddlewareInterface[]
     */
    private $privMiddlewares = [];

    /**
     * Global middlewares.
     * 
     * @var Route[]
     */
    private $routes = [];

    /**
     * Global middlewares.
     * 
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * The route parser for collection.
     * 
     * @var RouteParserInterface
     */
    private $routeParser;

    /**
     * @param CollectionInterface $collection
     * @param string $dispathDataFlag
     */
    public function __construct(CollectionInterface $collection = null, string $dispathDataFlag = DispatchInformationInterface::class)
    {
        $this->collection = $collection ?? new Collector();
        $this->dispatcher = new Dispatcher($this->collection);
        $this->dispathDataFlag = $dispathDataFlag;
    }

    /**
     * Add a router middleware.
     * 
     * @param MiddlewareInterface $middleware
     * 
     * @return MiddlewareInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareInterface
    {
        $this->middlewares[] = $middleware;

        foreach ($this->routes as $route) {
            $route->addMiddleware($middleware);
        }

        return $middleware;
    }

    /**
     * Attach data to collection.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param MiddlewareInterface[] $middlewares
     * @param string|null $name
     * 
     * @return Route
     */
    public function withRoute($methods, string $pattern, $handler, array $middlewares = [], string $name = null): Route
    {
        $pattern = $this->privPrefix . $pattern;
        $middlewares = array_merge($this->middlewares, $this->privMiddlewares, $middlewares);

        $route = new Route($methods, $pattern, $handler, $middlewares, $name);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Create a route group.
     * 
     * @param string $prefixPattern
     * @param MiddlewareInterface[] $middlewares
     * @param callable $callback
     */
    public function withGroup(string $prefixPattern, array $middlewares = [], callable $callback)
    {
        $prevPrefix = $this->privPrefix;
        $privMiddlewares = $this->privMiddlewares;
        $this->privPrefix = $this->privPrefix . $prefixPattern;
        $this->privMiddlewares = array_merge($this->privMiddlewares, $middlewares);

        call_user_func(\Closure::fromCallable($callback), $this);
        $this->privPrefix = $prevPrefix;
        $this->privMiddlewares = $privMiddlewares;
    }

    /**
     * Get the route parser for collection.
     * 
     * @return RouteParserInterface
     */
    public function getRouteParser(): RouteParserInterface
    {
        if (!$this->routeParser) {
            $this->routeParser = new RouteParser($this->collection);
        }
        return $this->routeParser;
    }

    /**
     * Dispatch request.
     * 
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * 
     * @throws HttpMethodNotAllowedException
     * @throws HttpNotFoundException
     * @throws RuntimeException
     * 
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->attachCollection();
        $url = (string) $request->getUri();
        $httpMethod = $request->getMethod();
        $result = $this->dispatcher->dispatch($httpMethod, $url);

        switch ($result[0]) {
            case Dispatcher::STATUS_OK:
                list($_, $routeHandler, $options, $arguments) = $result;
                $dispatchInformation = new DispatchInformation($routeHandler, $options['middlewares'], $arguments);
                $request = $request->withAttribute($this->dispathDataFlag, $dispatchInformation);
                return $handler->handle($request);
            case Dispatcher::STATUS_ERROR:
                if (Dispatcher::ERROR_METHOD_NOT_ALLOWED === $result[1]) {
                    throw new HttpMethodNotAllowedException('405 Method Not Allowed.', $result[2]);
                } elseif (Dispatcher::ERROR_NOT_FOUND === $result[1]) {
                    throw new HttpNotFoundException('404 Not Found.');
                }
        }

        throw new RuntimeException('Unknow error from router.');
    }

    /**
     * Attach collection with routes.
     */
    private function attachCollection()
    {
        foreach ($this->routes as $route) {
            $options = [];
            $options['middlewares'] = $route->getMiddlewares();
            $name = $route->getName();
            if ($name) {
                $options['name'] = $name;
            }
            $handler = $route->getHandler();
            $pattern = $route->getPattern();
            $methods = $route->getHttpMethods();
            $this->collection->attach($methods, $pattern, $handler, $options);
        }
    }
}
