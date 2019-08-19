<?php

namespace ConstanzeStandard\Fluff;

trait HttpRouteHelperTrait
{
    public function get($pattern, $controller, array $conditions = [])
    {
        $this->route('GET', $pattern, $controller, $conditions);
    }

    public function post($pattern, $controller, array $conditions = [])
    {
        $this->route('POST', $pattern, $controller, $conditions);
    }

    public function delete($pattern, $controller, array $conditions = [])
    {
        $this->route('DELETE', $pattern, $controller, $conditions);
    }

    public function put($pattern, $controller, array $conditions = [])
    {
        $this->route('PUT', $pattern, $controller, $conditions);
    }
}
