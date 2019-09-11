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

use ConstanzeStandard\Fluff\Component\RouteData;
use ConstanzeStandard\Fluff\Traits\MiddlewareHandlerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The router request handler.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class RouteHandler implements RequestHandlerInterface
{
    use MiddlewareHandlerTrait;

    /**
     * The injectable invoker.
     * 
     * @var string Default is `route`.
     */
    private $routeFlag;

    /**
     * The child handler definition.
     * 
     * @var callable
     */
    private $definition;

    /**
     * @param string $attributeName Default is `route`.
     */
    public function __construct(callable $definition, string $routeFlag = 'ROUTE_FLAG')
    {
        $this->definition = $definition;
        $this->routeFlag = $routeFlag;
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
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $routeData = $request->getAttribute($this->routeFlag);

        if ($routeData instanceof RouteData) {
            $routeHandler = $routeData->getHandler();
            $middlewares = $routeData->getMiddlewares();
            $arguments = $routeData->getArguments();

            $childHandler = call_user_func($this->definition, $routeHandler, $arguments);
            return $this->handleWithMiddlewares($middlewares, $request, $childHandler);
        }

        throw new \RuntimeException('The `' . $this->routeFlag . '` attribute is not exist.');
    }
}
