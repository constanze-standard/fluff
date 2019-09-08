<?php

namespace ConstanzeStandard\Fluff\Interfaces;

interface RouteableInterface
{
    /**
     * Attach data to collection.
     *
     * @param array|string $methods
     * @param string $pattern
     * @param \Closure|array|string $handler
     * @param array $middlewares
     * @param string|null $name
     * 
     * @throws \InvalidArgumentException
     */
    public function withRoute($methods, string $pattern, $handler, array $middlewares = [], string $name = null);

    /**
     * Create a route group.
     * 
     * @param string $prefixPattern
     * @param array $middlewares
     * @param callable $callback
     */
    public function withGroup(string $prefixPattern, array $middlewares = [], callable $callback);
}
