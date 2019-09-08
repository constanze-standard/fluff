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

use ConstanzeStandard\Fluff\Component\HttpRouteHelperTrait;
use ConstanzeStandard\Fluff\Exception\MethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use ConstanzeStandard\Fluff\Interfaces\RouteableInterface;
use ConstanzeStandard\Route\Collector;
use ConstanzeStandard\Route\Dispatcher;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Router middleware.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class RouterMiddleware implements MiddlewareInterface, RouteableInterface
{
    use HttpRouteHelperTrait;

    const ATTRIBUTE_NAME = 'route';

    /**
     * The route collection.
     * 
     * @var CollectionInterface
     */
    private $collection;

    /**
     * Previous pattern prefix
     * 
     * @var string
     */
    private $privPrefix = '';

    /**
     * Previous middlewares.
     * 
     * @var array
     */
    private $privMiddlewares = [];

    /**
     * @param CollectionInterface $collection
     */
    public function __construct(CollectionInterface $collection = null)
    {
        $this->collection = $collection ?? new Collector();
    }

    /**
     * Attach data to collection.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param array $middlewares
     * @param string|null $name
     * 
     * @throws \InvalidArgumentException
     */
    public function withRoute($methods, string $pattern, $handler, array $middlewares = [], string $name = null)
    {
        $pattern = $this->privPrefix . $pattern;
        $middlewares = array_merge_recursive($this->privMiddlewares, $middlewares);
        $options = [];
        $options['middlewares'] = $middlewares;
        if ($name) {
            $options['name'] = $name;
        }
        $this->collection->attach($methods, $pattern, $handler, $options);
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
        $prevPrefix = $this->privPrefix;
        $privMiddlewares = $this->privMiddlewares;
        $this->privPrefix = $this->privPrefix . $prefixPattern;
        $this->privMiddlewares = array_merge_recursive($this->privMiddlewares, $middlewares);

        call_user_func(\Closure::fromCallable($callback), $this);
        $this->privPrefix = $prevPrefix;
        $this->privMiddlewares = $privMiddlewares;
    }

    /**
     * Dispatch request.
     * 
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * 
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws RuntimeException
     * 
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $dispatcher = new Dispatcher($this->collection);
        $url = (string) $request->getUri();
        $httpMethod = $request->getMethod();
        $result = $dispatcher->dispatch($httpMethod, $url);

        switch ($result[0]) {
            case Dispatcher::STATUS_OK:
                list($_, $routeHandler, $options, $params) = $result;
                $request = $request->withAttribute('route', [$routeHandler, $options['middlewares'], $params]);
                return $handler->handle($request);
            case Dispatcher::STATUS_ERROR:
                if (Dispatcher::ERROR_METHOD_NOT_ALLOWED === $result[1]) {
                    throw new MethodNotAllowedException('405 Method Not Allowed.', $result[2]);
                } elseif (Dispatcher::ERROR_NOT_FOUND === $result[1]) {
                    throw new NotFoundException('404 Not Found.');
                }
        }

        throw new RuntimeException('Unknow error from router.');
    }
}
