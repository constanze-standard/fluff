<?php

namespace ConstanzeStandard\Fluff;

use ConstanzeStandard\Fluff\Conponent\HttpRouteHelperTrait;
use ConstanzeStandard\Fluff\Conponent\HttpRouter;
use ConstanzeStandard\Fluff\Interfaces\HttpRouterInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteableInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteParserInterface;
use ConstanzeStandard\Fluff\Service\RouteParser;
use ConstanzeStandard\Route\Collector;
use ConstanzeStandard\Route\Dispatcher;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use ConstanzeStandard\Route\Interfaces\DispatcherInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterProxy implements RouteableInterface
{
    use HttpRouteHelperTrait;

    /**
     * Globel container.
     * 
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * settings
     * 
     * @var array
     */
    private $settings;

    /**
     * Http router.
     * 
     * @var HttpRouterInterface
     */
    private $httpRouter = [];

    /**
     * Route collection.
     * 
     * @var CollectionInterface
     */
    private $routeCollection;

    /**
     * Route dispatcher.
     * 
     * @var DispatcherInterface
     */
    private $routeDispatcher;

    /**
     * Route parser service.
     * 
     * @var RouteParserInterface
     */
    private $routeParser;

    /**
     * Route cache file path or false.
     * 
     * @var bool|string
     */
    private $routeCache;

    /**
     * Filters map.
     * 
     * @var array
     */
    private $filtersMap = [];

    /**
     * @param HttpRouterInterface $httpRouter
     * @param bool|string $routeCache
     * @param string $basePath
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->settings = $container->has('settings') ? $container->get('settings') : [];
    }

    /**
     * Get the PSR-11 container.
     * 
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get http router.
     * 
     * @return HttpRouterInterface
     */
    public function getHttpRouter(): HttpRouterInterface
    {
        if (!$this->httpRouter) {
            $collection = $this->getRouteCollection();
            $this->httpRouter = new HttpRouter($collection, $this->getRouteDispatcher());
        }
        return $this->httpRouter;
    }

    /**
     * Get route parser service.
     * 
     * @return RouteParserInterface
     */
    public function getRouteParser()
    {
        if (!$this->routeParser) {
            $container = $this->getContainer();
            if ($container->has(RouteParserInterface::class)) {
                $this->routeParser = $container->get(RouteParserInterface::class);
            } else {
                $basePath = $this->settings['base_path'] ?? '';
                $this->routeParser = new RouteParser($this->getRouteCollection(), $basePath);
            }
        }
        return $this->routeParser;
    }

    /**
     * Attach data to collector.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     * 
     * @throws \InvalidArgumentException
     */
    public function withRoute($methods, string $pattern, $controller, array $options = [])
    {
        $this->getHttpRouter()->withRoute($methods, $pattern, $controller, $options);
    }

    /**
     * Create a route group.
     * 
     * @param string $pattern
     * @param array $data
     * @param callable $callback
     */
    public function withGroup(string $prefixPattern, array $options = [], callable $callback)
    {
        if (array_key_exists('name', $options)) {
            unset($options['name']);
        }
        $this->getHttpRouter()->withGroup($prefixPattern, $options, $callback);
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
    protected function verifyFilters(ServerRequestInterface $serverRequest, array $filters, array $params)
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
     * Get route dispatcher.
     * 
     * @return DispatcherInterface
     */
    private function getRouteDispatcher()
    {
        if (!$this->routeDispatcher) {
            $container = $this->getContainer();
            if ($container->has(DispatcherInterface::class)) {
                $this->routeDispatcher = $container->get(DispatcherInterface::class);
            } else {
                $this->routeDispatcher = new Dispatcher($this->getRouteCollection());
            }
        }
        return $this->routeDispatcher;
    }

    /**
     * Get route collection.
     * 
     * @return CollectionInterface
     */
    private function getRouteCollection()
    {
        if (!$this->routeCollection) {
            $container = $this->getContainer();
            if ($container->has(CollectionInterface::class)) {
                $this->routeCollection = $container->get(CollectionInterface::class);
            } else {
                $withCache = $this->settings['route_cache'] ?? false;
                $this->routeCollection = new Collector(['withCache' => $withCache]);
            }
        }
        return $this->routeCollection;
    }
}
