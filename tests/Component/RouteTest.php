<?php

use ConstanzeStandard\Fluff\Component\Route;
use ConstanzeStandard\Fluff\Component\RouteParser;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouteTest extends AbstractTest
{
    public function testGetName()
    {
        $route = new Route('GET', '/', 'Target');
        $result = $route->setName('root');
        $this->assertEquals($result, $route);
        $result = $route->getName();
        $this->assertEquals($result, 'root');
    }

    public function testGetMiddleware()
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $route = new Route('GET', '/', 'Target');
        $result = $route->addMiddleware($middleware);
        $this->assertEquals($result, $route);
        $result = $route->getMiddlewares();
        $this->assertEquals($result, [$middleware]);
    }

    public function testGetHandlerPatternAndHttpMethods()
    {
        $route = new Route('GET', '/', 'Target');
        $result = $route->getHandler();
        $this->assertEquals($result, 'Target');

        $result = $route->getPattern();
        $this->assertEquals($result, '/');

        $result = $route->getHttpMethods();
        $this->assertEquals($result, 'GET');
    }
}
