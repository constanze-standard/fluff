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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Catch the exception and generate the error response.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class ExceptionCaptor implements MiddlewareInterface
{
    /**
     * Exception handlers.
     * 
     * @var callable[]
     */
    private array $exceptionHandlers = [];

    /**
     * Process an incoming server request.
     *
     * Catch the exception and generate the error response.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            return $this->exceptionHandlerProcess($request, $e);
        }
    }

    /**
     * Add a exception handler.
     * 
     * @param string $typeName
     * @param callable $handler
     */
    public function withExceptionHandler(string $typeName, callable $handler)
    {
        $this->exceptionHandlers[$typeName] = $handler;
    }

    /**
     * Process exception handler.
     *
     * @param ServerRequestInterface $request
     * @param \Throwable $e
     *
     * @return ResponseInterface
     * @throws \Throwable
     */
    private function exceptionHandlerProcess(ServerRequestInterface $request, \Throwable $e): ResponseInterface
    {
        $response = null;
        $className = get_class($e);
        if (array_key_exists($className, $this->exceptionHandlers)) {
            $handler = $this->exceptionHandlers[$className];
            $response = call_user_func($handler, $request, $e);
        }

        if (!$response) {
            foreach ($this->exceptionHandlers as $className => $handler) {
                if (is_a($e, $className)) {
                    $response = call_user_func($handler, $request, $e);
                    break;
                }
            }
        }

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        throw $e;
    }
}
