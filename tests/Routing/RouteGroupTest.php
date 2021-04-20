<?php

use ConstanzeStandard\Fluff\Routing\Route;
use ConstanzeStandard\Fluff\Routing\RouteGroup;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouteGroupTest extends AbstractTest
{
    public function testAdd()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $routeGroup = new RouteGroup('/foo', [$middleware1]);
        $route = $routeGroup->add(
            ['GET'], '/bar', 'handler', [$middleware2], 'name'
        );
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/foo/bar', $route->getPattern());
        $this->assertEquals($route->getMiddlewares(), [$middleware1, $middleware2]);
        $this->assertEquals('name', $route->getName());
    }

    public function testAddWithSetPrefix()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $routeGroup = new RouteGroup('/foo', [$middleware1]);
        $routeGroup->setPrefix('/abc');
        $route = $routeGroup->add(
            ['GET'], '/bar', 'handler', [$middleware2], 'name'
        );
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/abc/bar', $route->getPattern());
        $this->assertEquals($route->getMiddlewares(), [$middleware1, $middleware2]);
        $this->assertEquals('name', $route->getName());
    }

    public function testGetRoutes()
    {
        /**
         * @var Route $route1
         * @var Route $route2
         */
        $route1 = $this->createMock(Route::class);
        $route2 = $this->createMock(Route::class);
        $routeGroup = new RouteGroup('/foo');
        $routeGroup->addRoute($route1);
        $routeGroup->addRoute($route2);
        $routes = $routeGroup->getRoutes();
        $this->assertEquals($routes, [$route1, $route2]);
    }

    public function testTraitGet()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $routeGroup = new RouteGroup('/foo', [$middleware1]);
        $route = $routeGroup->get(
            '/bar', 'handler', [$middleware2], 'name'
        );
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/foo/bar', $route->getPattern());
        $this->assertEquals($route->getMiddlewares(), [$middleware1, $middleware2]);
        $this->assertEquals('name', $route->getName());
    }

    public function testTraitPost()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $routeGroup = new RouteGroup('/foo', [$middleware1]);
        $route = $routeGroup->post(
            '/bar', 'handler', [$middleware2], 'name'
        );
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/foo/bar', $route->getPattern());
        $this->assertEquals($route->getMiddlewares(), [$middleware1, $middleware2]);
        $this->assertEquals('name', $route->getName());
    }

    public function testTraitPut()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $routeGroup = new RouteGroup('/foo', [$middleware1]);
        $route = $routeGroup->put(
            '/bar', 'handler', [$middleware2], 'name'
        );
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/foo/bar', $route->getPattern());
        $this->assertEquals($route->getMiddlewares(), [$middleware1, $middleware2]);
        $this->assertEquals('name', $route->getName());
    }

    public function testTraitDelete()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $routeGroup = new RouteGroup('/foo', [$middleware1]);
        $route = $routeGroup->delete(
            '/bar', 'handler', [$middleware2], 'name'
        );
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/foo/bar', $route->getPattern());
        $this->assertEquals($route->getMiddlewares(), [$middleware1, $middleware2]);
        $this->assertEquals('name', $route->getName());
    }

    public function testTraitOptions()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $routeGroup = new RouteGroup('/foo', [$middleware1]);
        $route = $routeGroup->options(
            '/bar', 'handler', [$middleware2], 'name'
        );
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/foo/bar', $route->getPattern());
        $this->assertEquals($route->getMiddlewares(), [$middleware1, $middleware2]);
        $this->assertEquals('name', $route->getName());
    }

    public function testDerive()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $routeGroup = new RouteGroup('/foo', [$middleware1]);
        $result = $routeGroup->derive('/bar', [$middleware2]);
        $this->assertInstanceOf(RouteGroup::class, $result);
        $result->add('GET', '/abc', 'handler');
        $route = $result->getRoutes()[0];
        $this->assertEquals('/foo/bar/abc', $route->getPattern());
        $this->assertEquals([$middleware1, $middleware2], $route->getMiddlewares());
    }
}
