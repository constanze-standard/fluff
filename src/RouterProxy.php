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

use ConstanzeStandard\Fluff\Component\HttpRouteHelperTrait;
use ConstanzeStandard\Fluff\Component\HttpRouter;
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
    protected $httpRouter = [];

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
                $settings = $this->getSettings();
                $basePath = $settings['base_path'] ?? '';
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
            if (is_string($name) && array_key_exists($name, $this->filtersMap)) {
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
     * Get current settings.
     * 
     * @return array
     */
    private function getSettings()
    {
        if (! $this->settings) {
            $this->settings = $this->container->has('settings') ? $this->container->get('settings') : [];
        }
        return $this->settings;
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
                $settings = $this->getSettings();
                $withCache = $settings['route_cache'] ?? false;
                $this->routeCollection = new Collector(['withCache' => $withCache]);
            }
        }
        return $this->routeCollection;
    }
}
