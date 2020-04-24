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

namespace ConstanzeStandard\Fluff\Routing;

use ConstanzeStandard\Fluff\Exception\HttpMethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\HttpNotFoundException;
use ConstanzeStandard\Fluff\Interfaces\RouteGroupInterface;
use ConstanzeStandard\Fluff\Interfaces\RouterInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteServiceInterface;
use ConstanzeStandard\Fluff\Traits\HttpRouteHelperTrait;
use ConstanzeStandard\Routing\Interfaces\RouteCollectionInterface;
use ConstanzeStandard\Routing\Matcher;
use ConstanzeStandard\Routing\RouteCollection;
use Psr\Http\Message\ServerRequestInterface;
use SplObjectStorage;

/**
 * The http route.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class Router extends RouteGroupProxy implements RouterInterface
{
    use HttpRouteHelperTrait;

    /**
     * The route collection.
     * 
     * @var RouteCollectionInterface
     */
    private RouteCollectionInterface $collection;

    /**
     * The route service for collection.
     * 
     * @var RouteServiceInterface
     */
    private RouteServiceInterface $routeService;

    /**
     * Group handlers.
     * 
     * @var SplObjectStorage
     */
    private SplObjectStorage $groupHandlers;

    /**
     * Global middlewares.
     * 
     * @var RouteGroupInterface[]
     */
    private array $routeGroups = [];

    /**
     * @param RouteCollectionInterface $routeCollection
     */
    public function __construct(?RouteCollectionInterface $routeCollection = null)
    {
        parent::__construct(new RouteGroup());
        $this->collection = $routeCollection ?? new RouteCollection();
        $this->groupHandlers = new SplObjectStorage();
        $this->routeService = RouteService::fromRoutes($this->collection);
    }

    /**
     * Get the route collection.
     * 
     * @return RouteCollectionInterface
     */
    public function getRouteCollection(): RouteCollectionInterface
    {
        return $this->collection;
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
        $this->routeGroups[] = $routeGroup;
        $this->groupHandlers[$routeGroup] = $callback;
        return $routeGroup;
    }

    /**
     * Match request or fail.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return array [$options, $routeHandler, $arguments]
     * 
     * @throws HttpMethodNotAllowedException
     * @throws HttpNotFoundException
     */
    public function matchOrFail(ServerRequestInterface $request): array
    {
        $this->attachRouteCollection($this->getRootGroup(), $this->routeGroups);
        $result = (new Matcher($this->collection))->match(
            $request->getMethod(), (string) $request->getUri()->getPath()
        );

        if (Matcher::STATUS_OK === $result[0]) {
            [ ,$options, $routeHandler, $arguments] = $result;
            return [$options, $routeHandler, $arguments];
        }

        if (Matcher::ERROR_METHOD_NOT_ALLOWED === $result[1]) {
            throw new HttpMethodNotAllowedException('405 Method Not Allowed.', $result[2]);
        }

        throw new HttpNotFoundException('404 Not Found.');
    }

    /**
     * Convert groups to routes and fill the collection.
     * 
     * @param RouteGroupInterface $rootGroup
     * @param RouteGroupInterface[] $routeGroups
     */
    private function attachRouteCollection(RouteGroupInterface $rootGroup, array $routeGroups)
    {
        $this->addRouteGroupToCollection($rootGroup);

        foreach ($routeGroups as $routeGroup) {
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
