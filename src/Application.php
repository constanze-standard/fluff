<?php

namespace ConstanzeStandard\Fluff;

use Beige\Invoker\Interfaces\InvokerInterface;
use Beige\Invoker\Invoker;
use Beige\Psr11\Container;
use Beige\PSR15\RequestHandler;
use Beige\Route\Collection as RouteCollection;
use Beige\Route\Matcher;
use Beige\Route\MatcherResult;
use Closure;
use ConstanzeStandard\Fluff\Exception\MethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use ConstanzeStandard\Fluff\Service\RouteService;
use ErrorException;
use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

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
     * @var Beige\Invoker\Interfaces\InvokerInterface
     */
    private $invoker;

    /**
     * Route collection.
     * 
     * @var Beige\Route\Interfaces\CollectionInterface
     */
    private $routeCollection;

    /**
     * Route service.
     * 
     * @var ConstanzeStandard\Fluff\Parser\RouteService
     */
    private $routeService;

    /**
     * Filters map.
     * 
     * @var array
     */
    private $filtersMap = [];

    /**
     * Global middelwares.
     * 
     * @var array
     */
    private $middlewares = [];

    /**
     * The invoker type-hint handlers
     * This property only be used in Application.
     * 
     * @var array
     */
    private $typehintHandlers = [];

    /**
     * The default system settings.
     *
     * @var array
     */
    private $settings = [
        'default_controller_method' => 'index',
        'response_chunk_size' => 4096,
        'package_base_path' => '',
        'package_entry_file_name' => '__package__',
        'clean_output_buffer' => false,
        'exception_handlers' => []
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
     * Get the type-hint invoker.
     *
     * @return \Beige\Invoker\Interfaces\InvokerInterface
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
     * Get system settings.
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Route collection.
     * 
     * @return \Beige\Route\Interfaces\CollectionInterface
     */
    public function getRouteCollection()
    {
        if (! $this->routeCollection) {
            $container = $this->getContainer();
            $this->routeCollection = new RouteCollection($container);
        }
        return $this->routeCollection;
    }

    /**
     * Get route service.
     * 
     * @return \ConstanzeStandard\Fluff\Parser\RouteService
     */
    public function getRouteService()
    {
        if (! $this->routeService) {
            $routeCollection = $this->getRouteCollection();
            $this->routeService = new RouteService($routeCollection);
        }
        return $this->routeService;
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
     * Add a global middleware.
     * 
     * @param MiddlewareInterface $middleware
     */
    public function withMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Set exception handler with name.
     * 
     * @param string $name
     * @param callable $handler
     */
    public function withExceptionHandler(string $name, callable $handler)
    {
        $values = $this->settings['exception_handlers'];
        $values[$name] = $handler;
        $this->settings['exception_handlers'] = $values;
    }

    /**
     * Add an route.
     * 
     * @param array|string $methods
     * @param string $pattern
     * @param array|callable $controller
     * @param array $conditions
     */
    public function route($methods, $pattern, $controller, array $conditions = [])
    {
        $routeCollection = $this->getRouteCollection();
        $routeCollection->attach($methods, $pattern, [
            '_controller' => $controller,
            'conditions' => $conditions
        ]);
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
        $routeCollection = $this->getRouteCollection();
        $matcher = new Matcher($routeCollection);
        $method = $request->getMethod();
        /** @var MatcherResult $matcherResult */
        $matcherResult = $matcher->match($method, (string) $request->getUri());

        try {
            if ($matcherResult->hasError()) {
                $errorType = $matcherResult->getErrotType();
                if ($errorType === MatcherResult::ERROR_METHOD_NOT_ALLOWED) {
                    throw new MethodNotAllowedException();
                } elseif ($errorType === MatcherResult::ERROR_NOT_FOUND) {
                    throw new NotFoundException();
                }
            }

            $data = $matcherResult->getData();
            $params = $matcherResult->getParams();
            $conditions = $data['conditions'];

            if (
                array_key_exists('filters', $conditions) &&
                ! $this->processFilters($request, $conditions['filters'], $params)
            ) {
                throw new NotFoundException();
            }

            $middlewares = array_merge($this->middlewares, $conditions['middlewares'] ?? []);
            $stack = $this->getRequestHandlerStack($data['_controller'], $middlewares, $params);
            $response = $stack->handle($request);
        } catch (\Throwable $e) {
            $response = $this->exceptionHandlerProcess($e);
        } catch (\Exception $e) {
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
        /** @var Closure $kernel */
        $kernel = function (ServerRequestInterface $serverRequest) use ($controller, $params) {
            $this->typehintHandlers[ServerRequestInterface::class] = $serverRequest;
            $this->typehintHandlers[RequestInterface::class] = $serverRequest;
            return call_user_func($this, $controller, $params);
        };

        return RequestHandler::stack($kernel->bindTo($this), $middlewares);
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
