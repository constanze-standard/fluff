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

namespace ConstanzeStandard\Fluff;

use ConstanzeStandard\RequestHandler\Dispatcher as RequestDispatcher;
use ConstanzeStandard\RequestHandler\Interfaces\MiddlewareDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The entry of FLUFF micro framework for dispatch the request handler.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class Application implements MiddlewareDispatcherInterface
{
    /**
     * The request dispatcher.
     * 
     * @var MiddlewareDispatcherInterface
     */
    private MiddlewareDispatcherInterface $middlewareDispatcher;

    /**
     * Application constructor.
     * set the custom container.
     *
     * @param \Psr\Http\Server\RequestHandlerInterface $requestHandler
     */
    public function __construct(RequestHandlerInterface $requestHandler)
    {
        $this->middlewareDispatcher = new RequestDispatcher($requestHandler);
    }

    /**
     * Add a middleware.
     * 
     * @param MiddlewareInterface $middleware
     * 
     * @return MiddlewareInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareInterface
    {
        return $this->middlewareDispatcher->addMiddleware($middleware);
    }

    /**
     * Dispatch the request to middlewares.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->middlewareDispatcher->handle($request);

        if (strcasecmp($request->getMethod(), 'HEAD') === 0) {
            $body = $request->getBody();
            if ($body->isWritable() && $body->isSeekable()) {
                $body->rewind();
                $body->write('');
            }
        }
        return $response;
    }
}
