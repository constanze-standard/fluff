<?php

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
     * @var Beige\Invoker\Invoker
     */
    private $invoker;

    /**
     * Middlewares array.
     * 
     * @var array
     */
    private $outerMiddlewares = [];

    /**
     * The invoker type-hint handlers
     * This property only be used in Application.
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
    public function getInvoker()
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
     * Invoke the controller and inject dependencys.
     * 
     * @param ServerRequestInterface $request
     * @param array|callable $controller
     * @param array $params
     * @param array $options
     * 
     * @throws \InvalidArgumentException
     * 
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, $controller, array $params = [], array $options = []): ResponseInterface
    {
        $core = function (ServerRequestInterface $request) use ($controller, $params) {
            $this->typehintHandlers[ServerRequestInterface::class]
                = $this->typehintHandlers[RequestInterface::class]
                = $this->typehintHandlers[get_class($request)]
                = $request;

            $invoker = $this->getInvoker();
            if (is_array($controller)) {
                $instance = $invoker->new($controller[0]);
                $method = $controller[1] ?: 'index';
                return $invoker->callMethod($instance, $method, $params);
            }

            if (is_callable($controller)) {
                return $invoker->call($controller, $params);
            }

            throw new \InvalidArgumentException('Controller must be array or callable.');
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
            list($controller, $options, $params) = $this->dispachRequest($request);
            return call_user_func($this, $request, $controller, $params, $options);
        };
        $requestHandler = RequestHandler::stack($routerRequesthandler, $this->outerMiddlewares);
        return $requestHandler->handle($request);
    }

    /**
     * Match and get route data.
     * 
     * @param ServerRequestInterface $request
     * 
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * 
     * @return array [$controller, $options, $params]
     */
    private function dispachRequest(ServerRequestInterface $request)
    {
        $result = $this->getHttpRouter()->dispatch($request);
        if ($result[0] === Dispatcher::STATUS_OK) {
            list($_, $controller, $options, $params) = $result;
            if ($this->verifyFilters($request, (array)($options['filters'] ?? []), $params)) {
                return [$controller, $options, $params];
            }
        } elseif ($result[1] === Dispatcher::ERROR_METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException('405 Method Not Allowed.', $result[2]);
        }

        throw new NotFoundException('404 Not Found.');
    }
}
