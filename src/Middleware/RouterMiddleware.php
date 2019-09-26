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
use ConstanzeStandard\Fluff\Component\RouteGroupProxy;
use ConstanzeStandard\Fluff\Component\RouteService;
use ConstanzeStandard\Fluff\Exception\HttpMethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\HttpNotFoundException;
use ConstanzeStandard\Fluff\Interfaces\RouteGroupInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteServiceInterface;
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
class RouterMiddleware extends RouteGroupProxy implements MiddlewareInterface
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
     * @var RouteGroupInterface[]
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
     * @param RouteCollectionInterface|string|null $option
     */
    public function __construct($option = null)
    {
        parent::__construct(new RouteGroup());

        $this->collection = $option instanceof RouteCollectionInterface ?
            $option : new RouteCollection($option);

        $this->routeService = new RouteService(
            $this->collectionToRoutes($this->collection)
        );

        $this->groupHandlers = new SplObjectStorage();
    }

    /**
     * Get the routeService.
     * 
     * @return RouteServiceInterface
     */
    public function getRouteService(): RouteServiceInterface
    {
        return $this->routeService;
    }

    /**
     * Add a route group.
     * 
     * @param RouteGroupInterface $routeGroup
     * 
     * @return RouteGroupInterface
     */
    public function addRouteGroup(RouteGroupInterface $routeGroup): RouteGroupInterface
    {
        $this->routeGroups[] = $routeGroup;
        return $routeGroup;
    }

    /**
     * Create a route group.
     * 
     * @param callable $callback
     * @param string $prefix
     * @param MiddlewareInterface[] $middlewares
     * 
     * @return RouteGroupInterface
     */
    public function group(callable $callback, string $prefix = '', array $middlewares = []): RouteGroupInterface
    {
        $routeGroup = $this->deriveGroup($prefix, $middlewares);
        $this->groupHandlers[$this->addRouteGroup($routeGroup)] = $callback;
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
        if (Matcher::STATUS_OK === $result[0]) {
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
     * @return RouteInterface[]
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
        $this->addRouteGroupToCollection($this->getRootGroup());
        foreach ($this->routeGroups as $routeGroup) {
            call_user_func($this->groupHandlers[$routeGroup], $routeGroup);
            $this->addRouteGroupToCollection($routeGroup);
        }
    }

    /**
     * Add a route data to collection.
     * 
     * @param RouteGroupInterface $route
     */
    private function addRouteGroupToCollection(RouteGroupInterface $routeGroup)
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
