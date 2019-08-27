<?php

namespace ConstanzeStandard\Fluff\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface HttpRouterInterface
{
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
    public function withRoute($methods, string $pattern, $controller, array $data = []);

    /**
     * Create a route group.
     * 
     * @param string $pattern
     * @param array $data
     * @param callable $callback
     */
    public function withGroup(string $prefixPattern, array $data = [], callable $callback);

    /**
     * Get the route parse component.
     * 
     * @return RouteParserInterface
     */
    public function getRouteParser(): RouteParserInterface;

    /**
     * Dispatch request.
     * 
     * @param ServerRequestInterface $request
     * 
     * @return array Same with DispatcherInterface::dispatch
     */
    public function dispatch(ServerRequestInterface $request);
}
