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
use ConstanzeStandard\RequestHandler\Dispatcher as RequestDispatcher;

/**
 * The router request handler.
 * 
 * @author Alex <blldxt@gmail.com>
 * 
 * @abstract
 */
abstract class AbstractRouteHandler implements RequestHandlerInterface
{
    /**
     * @param string $attributeName Default is `route`.
     */
    public function __construct(string $attributeName = 'route')
    {
        $this->attributeName = $attributeName;
    }

    /**
     * Get RequestHandler from callable.
     * 
     * @abstract
     * 
     * @param callable|array $handler
     * @param array $params
     * 
     * @return RequestHandlerInterface
     */
    abstract protected function getRequestHandler($handler, array $params): RequestHandlerInterface;

    /**
     * Invoke handler from route.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $request->getAttribute($this->attributeName);

        if ($route) {
            list($routeHandler, $middlewares, $params) = $route;
            $requestDispatcher = new RequestDispatcher(
                $this->getRequestHandler($routeHandler, $params)
            );

            ksort($middlewares);
            foreach ($middlewares as $middleware) {
                $requestDispatcher->addMiddleware($middleware);
            }

            return $requestDispatcher->handle($request);
        }

        throw new \RuntimeException('The `' . $this->attributeName . '` attribute is not exist.');
    }
}
