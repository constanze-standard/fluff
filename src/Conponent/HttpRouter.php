<?php

namespace ConstanzeStandard\Fluff\Conponent;

use ConstanzeStandard\Fluff\Interfaces\CollectorInterface;
use ConstanzeStandard\Fluff\Interfaces\HttpRouterInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteParserInterface;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use ConstanzeStandard\Route\Interfaces\DispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpRouter implements HttpRouterInterface
{
    use HttpRouteHelperTrait;

    /**
     * Private pattern prefix
     * 
     * @var string
     */
    private $privPrefix = '';

    /**
     * Private data.
     * 
     * @var array
     */
    private $privData = [];

    /**
     * Route parser component.
     * 
     * @var RouteParser
     */
    private $routeParser;

    /**
     * @param CollectorInterface $collector
     * @param DispatcherInterface $dispatcher
     */
    public function __construct(CollectionInterface $collector, DispatcherInterface $dispatcher, $hostName = '')
    {
        $this->collector = $collector;
        $this->dispatcher = $dispatcher;
        $this->hostName = $hostName;
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
    public function withRoute($methods, string $pattern, $controller, array $data = [])
    {
        $pattern = $this->privPrefix . $pattern;
        $data = array_merge_recursive($this->privData, $data);
        $this->collector->attach($methods, $pattern, $controller, $data);
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
        $prevPrefix = $this->privPrefix;
        $privData = $this->privData;
        $this->privPrefix = $this->privPrefix . $prefixPattern;
        $this->privData = array_merge_recursive($this->privData, $data);

        call_user_func($callback, $this);

        $this->privPrefix = $prevPrefix;
        $this->privData = $privData;
    }

    /**
     * Get the route parse component.
     * 
     * @return RouteParserInterface
     */
    public function getRouteParser(): RouteParserInterface
    {
        if (! $this->routeParser) {
            $this->routeParser = new RouteParser($this->collector, $this->hostName);
        }
        return $this->routeParser;
    }

    /**
     * Dispatch request.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return array Same with DispatcherInterface::dispatch
     */
    public function dispatch(ServerRequestInterface $request)
    {
        return $this->dispatcher->dispatch($request->getMethod(), (string) $request->getUri());
    }
}
