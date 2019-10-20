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

use ConstanzeStandard\Fluff\Routing\Router;
use ConstanzeStandard\Fluff\Interfaces\RouterInterface;
use ConstanzeStandard\Fluff\Traits\MiddlewareHandlerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The router request handler.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class Dispatcher implements RequestHandlerInterface
{
    use MiddlewareHandlerTrait;

    /**
     * The child handler definition.
     * 
     * @var callable
     */
    private $definition;

    /**
     * The router.
     * 
     * @var RouterInterface
     */
    private $router;

    /**
     * @param string $attributeName Default is `route`.
     * TODO: 这里可以应该从外部传入一个 RouterInterface 实例，代理全部的路由操作，将路由最大限度的与 Dispatcher 分离
     */
    public function __construct(callable $definition, RouterInterface $router = null)
    {
        $this->router = $router ?? new Router();
        $this->definition = $definition;
    }

    /**
     * Get the router.
     * 
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Get route data from previous middleware.
     * &
     * Handle the request and inject parameters to route handler.
     * 
     * @param ServerRequestInterface $request
     * 
     * @throws \RuntimeException
     * 
     * @return ResponseInterface
     * 
     * @throws HttpMethodNotAllowedException
     * @throws HttpNotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->getRouter()->matchOrFail($request);
        [$options, $routeHandler, $arguments] = $result;
        $middlewares = $options['middlewares'] ?? [];
        $childHandler = call_user_func($this->definition, $routeHandler, $arguments);
        return $this->handleWithMiddlewares($middlewares, $request, $childHandler);
    }
}
