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

namespace ConstanzeStandard\Fluff\Traits;

use Psr\Http\Server\MiddlewareInterface;
use ConstanzeStandard\RequestHandler\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The router collection helper.
 * 
 * @author Alex <blldxt@gmail.com>
 */
trait MiddlewareHandlerTrait
{
    /**
     * Handle the request and return the response by route data.
     * 
     * @param MiddlewareInterface[] $middlewares
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $childHandler
     */
    private function handleWithMiddlewares(
        array $middlewares,
        ServerRequestInterface $request,
        RequestHandlerInterface $childHandler
    ): ResponseInterface
    {
        $requestDispatcher = new Dispatcher($childHandler);
        $requestDispatcher = $this->generateMiddlewareStack(
            $requestDispatcher,
            $middlewares
        );

        return $requestDispatcher->handle($request);
    }

    /**
     * Generate middleware stack from array of middlewares.
     * 
     * @param RequestDispatcher $requestDispatcher
     * @param MiddlewareInterface[] $middlewares
     * 
     * @return RequestDispatcher
     */
    private function generateMiddlewareStack(Dispatcher $requestDispatcher, $middlewares): Dispatcher
    {
        foreach ($middlewares as $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                $requestDispatcher->addMiddleware($middleware);
            }
        }
        return $requestDispatcher;
    }
}
