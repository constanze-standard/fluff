<?php

namespace ConstanzeStandard\Fluff\Conponent;

class Router
{
    use HttpRouteHelperTrait;

    /**
     * The router prefix.
     * 
     * @var string
     */
    private $prefix = '';

    /**
     * The default conditions.
     * 
     * @var array
     */
    private $conditions = [];

    /**
     * Routes of router.
     * 
     * @var array
     */
    private $routes = [];

    /**
     * Constructor of Router.
     * 
     * @param string $prefix
     * @param array $conditions
     */
    public function __construct(string $prefix = '', array $conditions = [])
    {
        $this->prefix = $prefix;
        $this->conditions = $conditions;
    }

    /**
     * Attach a route to collection.
     * 
     * @param array|string $methods
     * @param string $pattern
     * @param array|callable $controller
     * @param string|null $name
     * @param array $conditions
     */
    public function withRoute($methods, $pattern, $controller, string $name = null, array $conditions = [])
    {
        $pattern = $this->prefix . $pattern;
        $conditions = array_merge_recursive($this->conditions, $conditions);
        $this->routes[] = [$methods, $pattern, $controller, $name, $conditions];
    }

    /**
     * Create a route group.
     * 
     * @param string $prefix
     * @param array $conditions
     * @param callable $callable
     */
    public function withGroup(string $prefix, array $conditions, callable $callable)
    {
        $prevPrefix = $this->prefix;
        $prevConditions = $this->conditions;
        $this->prefix = $this->prefix . $prefix;
        $this->conditions = array_merge_recursive($this->conditions, $conditions);

        call_user_func($callable, $this);

        $this->prefix = $prevPrefix;
        $this->conditions = $prevConditions;
    }

    /**
     * Get routes data.
     * 
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
