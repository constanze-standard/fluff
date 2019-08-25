<?php

namespace ConstanzeStandard\Fluff;

use Beige\Invoker\Invoker;
use Beige\Psr11\Container;
use Beige\PSR15\RequestHandler;
use Closure;
use ConstanzeStandard\Fluff\Conponent\HttpRouteHelperTrait;
use ConstanzeStandard\Fluff\Conponent\HttpRouter;
use ConstanzeStandard\Fluff\Conponent\RouteParser;
use ConstanzeStandard\Fluff\Exception\MethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use ConstanzeStandard\Route\Collector;
use ConstanzeStandard\Route\Dispatcher;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// set_error_handler(function ($severity, $message, $file, $line) {
//     if (!(error_reporting() & $severity)) {
//         // This error code is not included in error_reporting
//         return;
//     }
//     throw new ErrorException($message, 0, $severity, $file, $line);
// });

class Application
{
    use HttpRouteHelperTrait;
    /**
     * Globel container.
     * 
     * @var Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * The type-hint invoker.
     *
     * @var Beige\Invoker\Invoker
     */
    private $invoker;

    /**
     * Filters map.
     * 
     * @var array
     */
    private $filtersMap = [];

    /**
     * The invoker type-hint handlers
     * This property only be used in Application.
     * 
     * @var array
     */
    private $typehintHandlers = [];

    /**
     * Route collection.
     * 
     * @var CollectionInterface
     */
    private $routeCollection;

    /**
     * The system http router.
     * 
     * @var HttpRouter
     */
    private $httpRouter;

    /**
     * Route parser component.
     * 
     * @var RouteParser
     */
    private $routeParser;

    /**
     * The default system settings.
     *
     * @var array
     */
    private $settings = [
        'default_controller_method' => 'index',
        'response_chunk_size' => 4096,
        'clean_output_buffer' => false,
        'exception_handlers' => [],
        'route_cache' => false,
    ];

    /**
     * Flush output buffers if the buffer is flushable.
     */
    private static function flushOutputBuffers()
    {
        $status = ob_get_status(true);
        $level = ob_get_level();
        $flushFlags = PHP_OUTPUT_HANDLER_REMOVABLE | PHP_OUTPUT_HANDLER_FLUSHABLE;
        $cleanFlags = PHP_OUTPUT_HANDLER_REMOVABLE | PHP_OUTPUT_HANDLER_CLEANABLE;

        while ($level-- > 0) {
            $currentStatus = $status[$level];
            if (
                (isset($currentStatus['del']) && $currentStatus['del']) ||
                (
                    isset($currentStatus['flags']) &&
                    (($currentStatus['flags'] & $flushFlags) === $flushFlags)
                )
            ) {
                ob_end_flush();
            } elseif (
                isset($currentStatus['flags']) &&
                (($currentStatus['flags'] & $cleanFlags) === $cleanFlags)
            ) {
                ob_end_clean();
            }
        }
    }

    /**
     * Application constructor.
     * set the container and settings.
     *
     * @param ContainerInterface $container
     * @param array $settings
     */
    public function __construct(ContainerInterface $container = null, array $settings = [])
    {
        $this->container = $container ?? new Container();
        $this->settings = $settings + $this->settings;
    }

    /**
     * Get the container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get system settings.
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Get the type-hint invoker.
     *
     * @return \Beige\Invoker\Invoker
     */
    public function getInvoker()
    {
        if (! $this->invoker) {
            $container = $this->getContainer();
            $this->invoker = new Invoker($container);
            $this->invoker->setDefaultTypehintHandler(function($typeName, $throwException) {
                if (array_key_exists($typeName, $this->typehintHandlers)) {
                    return $this->typehintHandlers[$typeName];
                }
                $throwException();
            });
        }
        return $this->invoker;
    }

    /**
     * Get the route parse component.
     * 
     * @return RouteParser
     */
    public function getRouteParser(): RouteParser
    {
        if (! $this->routeParser) {
            $collection = $this->getRouteCollection();
            $this->routeParser = new RouteParser($collection);
        }
        return $this->routeParser;
    }

    /**
     * Add a route.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     * 
     * @throws \InvalidArgumentException
     */
    public function withRoute($methods, string $pattern, $controller, array $data = [])
    {
        $httpRouter = $this->getHttpRouter();
        $httpRouter->withRoute($methods, $pattern, $controller, $data);
    }

    /**
     * Create a route group.
     * 
     * @param string $pattern
     * @param array $data
     * @param callable $callback
     */
    public function withGroup(string $prefixPattern, array $data = [], callable $callback)
    {
        $httpRouter = $this->getHttpRouter();
        if (array_key_exists('name', $data)) {
            unset($data['name']);
        }
        $httpRouter->withGroup($prefixPattern, $data, $callback);
    }

    /**
     * Add a filter to map.
     * 
     * @param string $name
     * @param callable $callable
     */
    public function withFilter(string $name, callable $callable)
    {
        $this->filtersMap[$name] = $callable;
    }

    /**
     * Invoke the controller and inject dependencys.
     * 
     * @param array|callable $controller
     * @param array $params
     * 
     * @throws \Exception
     * 
     * @return ResponseInterface|null
     */
    public function __invoke($controller, array $params = []): ?ResponseInterface
    {
        $invoker = $this->getInvoker();
        if (is_array($controller)) {
            $object = $invoker->new($controller[0]);
            $settings = $this->getSettings();
            $controllerMethod = $settings['default_controller_method'];
            return $invoker->callMethod($object, $controller[1] ?? $controllerMethod, $params);
        } elseif (is_callable($controller)) {
            return $invoker->call($controller, $params);
        }

        throw new \Exception('Controller must be string or callable object.');
    }

    /**
     * Start a http process with server-side request.
     * 
     * @param ServerRequestInterface $request
     * 
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     */
    public function start(ServerRequestInterface $request)
    {
        $httpRouter = $this->getHttpRouter();
        $result = $httpRouter->dispatch($request);

        try {
            if ($result[0] === Dispatcher::STATUS_ERROR) {
                $errorType = $result[1];
                if (Dispatcher::ERROR_ALLOWED_METHODS === $errorType) {
                    throw new MethodNotAllowedException();
                } elseif (Dispatcher::ERROR_NOT_FOUND === $errorType) {
                    throw new NotFoundException();
                }
            } elseif ($result[0] === Dispatcher::STATUS_OK) {
                list($status, $controller, $data, $params) = $result;
                if (array_key_exists('filters', $data) && ! $this->processFilters($request, $data['filters'], $params)) {
                    throw new NotFoundException();
                }
                $stack = $this->getRequestHandlerStack($controller, $data['middlewares'] ?? [], $params);
                $response = $stack->handle($request);
            } else {
                throw new NotFoundException();
            }
        } catch (\Throwable $e) {
            $response = $this->exceptionHandlerProcess($e);
        }
        $this->outputResponse($response);
    }

    /**
     * Output http accept from response
     * 
     * @param ResponseInterface $response
     */
    public function outputResponse(ResponseInterface $response)
    {
        ob_start();
        $settings = $this->getSettings();
        if ($settings['clean_output_buffer']) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        $this->respondHeader($response);
        foreach ($this->respondContents($response) as $content) {
            echo $content;
            if (ob_get_level() > 0) {
                flush();
                ob_flush();
            }
        }

        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            static::flushOutputBuffers();
        }
    }

    /**
     * Process rules for route.
     * 
     * @param ServerRequestInterface $serverRequest
     * @param array $options
     * @param array $params
     * 
     * @throws \Exception
     * 
     * @return bool
     */
    private function processFilters(ServerRequestInterface $serverRequest, array $filters, array $params)
    {
        foreach ($filters as $name => $option) {
            $isPassed = true;
            if (array_key_exists($name, $this->filtersMap)) {
                $filter = $this->filtersMap[$name];
                $isPassed = $filter($serverRequest, $option, $params);
            } elseif (is_callable($option)) {
                $isPassed = $option($serverRequest, $params);
            }
            if (!$isPassed) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get a RequestHandler stack about middlewares.
     * 
     * @param callback|array $controller
     * @param array $middlewares
     * @param array $params
     */
    private function getRequestHandlerStack($controller, array $middlewares, array $params)
    {
        /** @var Closure $core */
        $core = function (ServerRequestInterface $serverRequest) use ($controller, $params) {
            $this->typehintHandlers[ServerRequestInterface::class] = $serverRequest;
            $this->typehintHandlers[RequestInterface::class] = $serverRequest;
            return call_user_func($this, $controller, $params);
        };

        $invoker = $this->getInvoker();
        $stack = new RequestHandler($core->bindTo($this));
        foreach ($middlewares as $middlewareName) {
            $middleware = $invoker->new($middlewareName);
            $stack = $stack->addMiddleware($middleware);
        }
        return $stack;
    }

    /**
     * Send accept header from response.
     * 
     * @param ResponseInterface $response
     */
    private function respondHeader(ResponseInterface $response)
    {
        if (!headers_sent()) {
            $version = $response->getProtocolVersion();
            $statusCode = $response->getStatusCode();
            $reasonPhrase = $response->getReasonPhrase();
            header(sprintf('HTTP/%s %s %s', $version, $statusCode, $reasonPhrase));

            foreach ($response->getHeaders() as $key => $headers) {
                $replace = 0 === strcasecmp($key, 'content-type');
                foreach ($headers as $header) {
                    header($key . ': ' . $header, $replace);
                }
            }
        }
    }

    /**
     * Get accept contents iterable.
     * 
     * @param ResponseInterface $response
     * 
     * @return iterable
     */
    private function respondContents(ResponseInterface $response): iterable
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }
        $settings = $this->getSettings();
        $chunkSize = $settings['response_chunk_size'];

        $contentLength  = $response->getHeaderLine('Content-Length');
        if (!$contentLength) {
            $contentLength = $body->getSize();
        }

        while ($contentLength > 0 && !$body->eof()) {
            $length = min((int)$chunkSize, (int)$contentLength);
            $contentLength -= $length;
            yield $body->read($length);
        }
    }

    /**
     * Get route collection.
     * 
     * @return CollectionInterface
     */
    private function getRouteCollection(): CollectionInterface
    {
        if (!$this->routeCollection) {
            $settings = $this->getSettings();
            $routeCache = $settings['route_cache'];
            $this->routeCollection = new Collector(['withCache' => $routeCache]);
        }
        return $this->routeCollection;
    }

    /**
     * Get http router.
     * 
     * @return HttpRouter
     */
    private function getHttpRouter(): HttpRouter
    {
        if (! $this->httpRouter) {
            $collector = $this->getRouteCollection();
            $dispacher = new Dispatcher($collector);
            $this->httpRouter = new HttpRouter($collector, $dispacher);
        }
        return $this->httpRouter;
    }

    /**
     * Process exception handler.
     * 
     * @param \Throwable $e
     * 
     * @throws \Throwable
     * 
     * @return ResponseInterface
     */
    private function exceptionHandlerProcess(\Throwable $e): ResponseInterface
    {
        $className = get_class($e);
        $settings = $this->getSettings();
        $exceptionHandlers = $settings['exception_handlers'];

        if (array_key_exists($className, $exceptionHandlers)) {
            $handler = $exceptionHandlers[$className];
            return $handler($e);
        } else {
            throw $e;
        }
    }
}
