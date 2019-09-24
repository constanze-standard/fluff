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
use ConstanzeStandard\Fluff\Component\RouteGroup;
use ConstanzeStandard\Fluff\Component\RouteService;
use ConstanzeStandard\Fluff\Exception\HttpMethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\HttpNotFoundException;
use ConstanzeStandard\Fluff\Traits\HttpRouteHelperTrait;
use ConstanzeStandard\Routing\Interfaces\RouteCollectionInterface;
use ConstanzeStandard\Routing\Matcher;
use ConstanzeStandard\Routing\RouteCollection;
use ConstanzeStandard\Standard\Http\Server\DispatchInformationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use SplObjectStorage;

/**
 * Router middleware.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class RouterMiddleware implements MiddlewareInterface
{
    use HttpRouteHelperTrait;

    /**
     * The dispatch data flag.
     * 
     * @var string
     */
    private $dispathDataFlag = DispatchInformationInterface::class;

    /**
     * The route collection.
     * 
     * @var RouteCollectionInterface
     */
    private $collection;

    /**
     * Global middlewares.
     * 
     * @var RouteGroup[]
     */
    private $routeGroups = [];

    /**
     * Global middlewares.
     * 
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * The route service for collection.
     * 
     * @var RouteService
     */
    private $routeService;

    /**
     * Group handlers.
     * 
     * @var SplObjectStorage
     */
    private $groupHandlers;

    /**
     * @param RouteCollectionInterface|null $collection
     */
    public function __construct(RouteCollectionInterface $collection = null)
    {
        $this->collection = $collection ?? new RouteCollection();
        $this->routeService = new RouteService(
            $collection ? $this->collectionToRoutes($this->collection) : []
        );

        $this->group = new RouteGroup('', $this->middlewares);
        $this->groupHandlers = new SplObjectStorage();
    }

    /**
     * Get the routeService.
     * 
     * @return RouteService
     */
    public function getRouteService(): RouteService
    {
        return $this->routeService;
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
        return $this->group->addMiddleware($middleware);
    }

    /**
     * Add a route to collection.
     * 
     * @param Route $route
     * 
     * @return Route
     */
    public function addRoute(Route $route)
    {
        return $this->group->addRoute($route);
    }

    /**
     * Register route data to collection.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param MiddlewareInterface[] $middlewares
     * @param string|null $name
     * 
     * @return Route
     */
    public function add($methods, string $pattern, $handler, array $middlewares = [], string $name = null): Route
    {
        return $this->group->add($methods, $pattern, $handler, $middlewares, $name);
    }

    /**
     * Create a route group.
     * 
     * @param callable $callback
     * @param string $prefix
     * @param MiddlewareInterface[] $middlewares
     */
    public function group(callable $callback, string $prefix = '', array $middlewares = [])
    {
        $middlewares = array_merge($this->middlewares, $middlewares);
        $routeGroup = new RouteGroup($prefix, $middlewares);
        $this->groupHandlers[$routeGroup] = $callback;
        $this->routeGroups[] = $routeGroup;
        return $routeGroup;
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
        $this->attachRouteCollection();
        $result = (new Matcher($this->collection))->match(
            $request->getMethod(), (string) $request->getUri()->getPath()
        );
        if ($result[0] == Matcher::STATUS_OK) {
            [ ,$options, $routeHandler, $arguments] = $result;
            $dispatchInformation = new DispatchInformation($routeHandler, $options['middlewares'] ?? [], $arguments);
            $request = $request->withAttribute($this->dispathDataFlag, $dispatchInformation);
            return $handler->handle($request);
        } elseif (Matcher::ERROR_METHOD_NOT_ALLOWED === $result[1]) {
            throw new HttpMethodNotAllowedException('405 Method Not Allowed.', $result[2]);
        }
        throw new HttpNotFoundException('404 Not Found.');
    }

    /**
     * Collection convert to routes.
     * 
     * @param RouteCollectionInterface $collection
     * 
     * @return Route[]
     */
    private function collectionToRoutes(RouteCollectionInterface $collection): array
    {
        $routes = [];
        $contents = $collection->getContents();
        foreach ($contents as $map) {
            foreach ($map as $method => $_contents) {
                foreach ($_contents as $content) {
                    [$pattern, $unserializableId, $serializable, ] = $content;
                    $handler = $collection->getUnserializableById($unserializableId);
                    $middlewares = $serializable['middlewares'] ?? [];
                    $name = $serializable['name'] ?? null;
                    $routes[] = new Route($method, $pattern, $handler, $middlewares, $name);
                }
            }
        }

        return $routes;
    }

    /**
     * Convert groups to routes and fill the collection.
     */
    private function attachRouteCollection()
    {
        $this->addRouteGroupToCollection($this->group);
        foreach ($this->routeGroups as $routeGroup) {
            call_user_func($this->groupHandlers[$routeGroup], $routeGroup);
            $this->addRouteGroupToCollection($routeGroup);
        }
    }

    /**
     * Add a route data to collection.
     * 
     * @param RouteGroup $route
     */
    private function addRouteGroupToCollection(RouteGroup $routeGroup)
    {
        foreach ($routeGroup->getRoutes() as $route) {
            $this->routeService->addRoute($route);
            $this->collection->add(
                $route->getHttpMethods(),
                $route->getPattern(),
                ['middlewares' => $route->getMiddlewares(),
                    'name' => $route->getName()],
                $route->getHandler()
            );
        }
    }
}
