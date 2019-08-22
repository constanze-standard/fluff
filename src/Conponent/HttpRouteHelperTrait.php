<?php

namespace ConstanzeStandard\Fluff\Conponent;

trait HttpRouteHelperTrait
{
    public function get($pattern, $controller, string $name = null, array $conditions = [])
    {
        $this->withRoute('GET', $pattern, $controller, $name, $conditions);
    }

    public function post($pattern, $controller, string $name = null, array $conditions = [])
    {
        $this->withRoute('POST', $pattern, $controller, $name, $conditions);
    }

    public function delete($pattern, $controller, string $name = null, array $conditions = [])
    {
        $this->withRoute('DELETE', $pattern, $controller, $name, $conditions);
    }

    public function put($pattern, $controller, string $name = null, array $conditions = [])
    {
        $this->withRoute('PUT', $pattern, $controller, $name, $conditions);
    }
}
