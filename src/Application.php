<?php
/**
 * Copyright 2019 Speed Sonic <blldxt@gmail.com>
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

use Beige\Invoker\Interfaces\InvokerInterface;
use Beige\Invoker\Invoker;
use Beige\Psr11\Container;
use Beige\PSR15\RequestHandler;
use ConstanzeStandard\Fluff\Exception\MethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use ConstanzeStandard\Route\Dispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class Application extends RouterProxy
{
    /**
     * The type-hint invoker.
     *
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * Global middlewares for application.
     * 
     * @var array
     */
    private $outerMiddlewares = [];

    /**
     * The invoker type-hint handlers
     * Only be used in Application.
     * 
     * @var array
     */
    private $typehintHandlers = [];

    /**
     * Application constructor.
     * set the custom container and http router.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        parent::__construct($container ?? new Container());
    }

    /**
     * Get the type-hint invoker.
     *
     * @return InvokerInterface
     */
    public function getInvoker(): InvokerInterface
    {
        if (! $this->invoker) {
            $this->invoker = new Invoker(
                $this->getContainer(), function($typeName, $throwException) {
                if (array_key_exists($typeName, $this->typehintHandlers)) {
                    return $this->typehintHandlers[$typeName];
                }
                $throwException();
            });
        }
        return $this->invoker;
    }

    /**
     * Add a global middleware.
     * 
     * @param MiddlewareInterface[] $middleware
     * 
     * @return MiddlewareInterface
     */
    public function withMiddleware(MiddlewareInterface $middleware)
    {
        $this->outerMiddlewares[] = $middleware;
        return $middleware;
    }

    /**
     * Invoke the handler and inject dependencys.
     * 
     * @param ServerRequestInterface $request
     * @param array|callable $handler
     * @param array $params
     * @param array $options
     * 
     * @throws \InvalidArgumentException
     * 
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, $handler, array $params = [], array $options = []): ResponseInterface
    {
        $core = function (ServerRequestInterface $request) use ($handler, $params) {
            $this->typehintHandlers[ServerRequestInterface::class]
                = $this->typehintHandlers[RequestInterface::class]
                = $this->typehintHandlers[get_class($request)]
                = $request;

            return $this->executeHandler($handler, $params);
        };

        $innerMiddlewares = (array)($options['middlewares'] ?? []);
        $requestHandler = RequestHandler::stack($core, $innerMiddlewares);
        return $requestHandler->handle($request);
    }

    /**
     * Start a http process with server-side request.
     * @see https://github.com/constanze-standard/router/blob/master/README.md
     * 
     * @param ServerRequestInterface $request
     * 
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * 
     * @return ResponseInterface
     */
    public function start(ServerRequestInterface $request): ResponseInterface
    {
        $routerRequesthandler = function (ServerRequestInterface $request) {
            list($handler, $options, $params) = $this->dispachRequestOrThrow($request);
            return call_user_func($this, $request, $handler, $params, $options);
        };
        $requestHandler = RequestHandler::stack($routerRequesthandler, $this->outerMiddlewares);
        return $requestHandler->handle($request);
    }

    /**
     * Call handler with type-hint injection.
     * 
     * @param array|callable $hadnler
     * @param array $params
     * 
     * @return ResponseInterface
     */
    private function executeHandler($hadnler, array $params = [])
    {
        $invoker = $this->getInvoker();
        if (is_array($hadnler)) {
            $instance = $invoker->new($hadnler[0]);
            $method = $hadnler[1] ?: 'index';
            return $invoker->callMethod($instance, $method, $params);
        }

        if (is_callable($hadnler)) {
            return $invoker->call($hadnler, $params);
        }

        throw new \InvalidArgumentException('Controller must be array or callable.');
    }

    /**
     * Match and get route data.
     * 
     * @param ServerRequestInterface $request
     * 
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * 
     * @return array [$handler, $options, $params]
     */
    private function dispachRequestOrThrow(ServerRequestInterface $request)
    {
        $result = $this->getHttpRouter()->dispatch($request);
        if ($result[0] === Dispatcher::STATUS_OK) {
            list($_, $handler, $options, $params) = $result;
            if ($this->verifyFilters($request, (array)($options['filters'] ?? []), $params)) {
                return [$handler, $options, $params];
            }
        } elseif ($result[1] === Dispatcher::ERROR_METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException('405 Method Not Allowed.', $result[2]);
        }

        throw new NotFoundException('404 Not Found.');
    }
}
