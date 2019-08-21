<?php

namespace ConstanzeStandard\Fluff;

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
     * @param array $conditions
     */
    public function route($methods, $pattern, $controller, array $conditions = [])
    {
        $pattern = $this->prefix . $pattern;
        $conditions = array_merge_recursive($this->conditions, $conditions);
        $this->routes[] = [$methods, $pattern, $controller, $conditions];
    }

    /**
     * Set a group.
     * 
     * @param string $prefix
     * @param array $conditions
     * @param callable $callable
     */
    public function group(string $prefix, array $conditions, callable $callable)
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
