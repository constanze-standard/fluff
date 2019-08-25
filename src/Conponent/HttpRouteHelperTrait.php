<?php

namespace ConstanzeStandard\Fluff\Conponent;

trait HttpRouteHelperTrait
{
    /**
     * Attach route to collector with `GET` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     */
    public function get($pattern, $controller, array $data = [])
    {
        $this->withRoute('GET', $pattern, $controller, $data);
    }

    /**
     * Attach route to collector with `POST` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     */
    public function post($pattern, $controller, array $data = [])
    {
        $this->withRoute('POST', $pattern, $controller, $data);
    }

    /**
     * Attach route to collector with `DELETE` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     */
    public function delete($pattern, $controller, array $data = [])
    {
        $this->withRoute('DELETE', $pattern, $controller, $data);
    }

    /**
     * Attach route to collector with `PUT` method.
     * 
     * @param string $pattern
     * @param \Closure|array|string $controller
     * @param array $data
     */
    public function put($pattern, $controller, array $data = [])
    {
        $this->withRoute('PUT', $pattern, $controller, $data);
    }
}
