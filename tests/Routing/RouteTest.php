<?php

use ConstanzeStandard\Fluff\Routing\Route;
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
        $this->assertEquals('root', $result);
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
        $this->assertEquals('Target', $result);

        $result = $route->getPattern();
        $this->assertEquals('/', $result);

        $result = $route->getHttpMethods();
        $this->assertEquals('GET', $result);
    }
}
