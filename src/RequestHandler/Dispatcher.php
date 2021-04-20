<?php

/**
 * Copyright 2019 Alex <omytty@126.com>
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

use ConstanzeStandard\Fluff\Exception\HttpMethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\HttpNotFoundException;
use ConstanzeStandard\Fluff\Routing\Router;
use ConstanzeStandard\Fluff\Interfaces\RouterInterface;
use ConstanzeStandard\Fluff\Traits\MiddlewareHandlerTrait;
use ConstanzeStandard\Routing\Interfaces\RouteCollectionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The router request handler.
 * 
 * @author Alex <omytty@126.com>
 */
class Dispatcher extends Router implements RequestHandlerInterface, RouterInterface
{
    use MiddlewareHandlerTrait;

    /**
     * The child handler definition.
     * 
     * @var callable
     */
    private $definition;

    /**
     * @param callable $definition
     * @param RouteCollectionInterface|null $routeCollection
     */
    public function __construct(callable $definition, ?RouteCollectionInterface $routeCollection = null)
    {
        $this->definition = $definition;
        parent::__construct($routeCollection);
    }

    /**
     * Get route data from previous middleware.
     * &
     * Handle the request and inject parameters to route handler.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     * 
     * @throws HttpMethodNotAllowedException
     * @throws HttpNotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        [$options, $routeHandler, $arguments] = $this->matchOrFail($request);
        $middlewares = $options['middlewares'] ?? [];
        $childHandler = call_user_func($this->definition, $routeHandler, $arguments);
        return $this->handleWithMiddlewares($middlewares, $request, $childHandler);
    }
}
