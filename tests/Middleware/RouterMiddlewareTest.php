<?php

use ConstanzeStandard\Fluff\Component\Route;
use ConstanzeStandard\Fluff\Component\RouteGroup;
use ConstanzeStandard\Fluff\Interfaces\RouteGroupInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteServiceInterface;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Routing\RouteCollection;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouterMiddlewareTest extends AbstractTest
{
    public function testGetRouteService()
    {
        $routerMiddleware = new RouterMiddleware();
        $result = $routerMiddleware->getRouteService();
        $this->assertInstanceOf(RouteServiceInterface::class, $result);
    }

    public function testAddMiddleware()
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $routeGroup = $this->createMock(RouteGroup::class);
        $routeGroup->expects($this->once())->method('addMiddleware')->willReturn($middleware);
        $routerMiddleware = new RouterMiddleware();
        $this->setProperty($routerMiddleware, 'routeGroup', $routeGroup);
        $result = $routerMiddleware->addMiddleware($middleware);
        $this->assertEquals($middleware, $result);
    }

    public function testAddRoute()
    {
        /** @var Route $route */
        $route = $this->createMock(Route::class);
        $routeGroup = $this->createMock(RouteGroup::class);
        $routeGroup->expects($this->once())->method('addRoute')->with($route)->willReturn($route);
        $routerMiddleware = new RouterMiddleware();
        $this->setProperty($routerMiddleware, 'routeGroup', $routeGroup);
        $result = $routerMiddleware->addRoute($route);
        $this->assertEquals($route, $result);
    }

    public function testAddWithPrefix()
    {
        /** @var Route $route */
        $route = $this->createMock(Route::class);
        $routerMiddleware = (new RouterMiddleware())->setPrefix('/foo');
        $result = $routerMiddleware->add('GET', '/bar', 'handler');
        $this->assertInstanceOf(Route::class, $result);
        $this->assertEquals($result->getPattern(), '/foo/bar');
    }

    public function testGroup()
    {
        /** @var MiddlewareInterface $middleware1 */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        /** @var MiddlewareInterface $middleware2 */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $func = function(RouteGroupInterface $route) {
            $route->add('GET', '/g1', 'handler');
        };
        $routeGroup = $this->createMock(RouteGroupInterface::class);
        $routerMiddleware = new RouterMiddleware();
        $routerMiddleware->addMiddleware($middleware2);
        $this->setProperty($routerMiddleware, 'routeGroup', $routeGroup);
        $result = $routerMiddleware->group($func, '/foo', [$middleware1]);
        $this->assertInstanceOf(RouteGroupInterface::class, $result);
    }

    public function testProcessWithStatusOK()
    {
        $routerMiddleware = new RouterMiddleware();
        $routerMiddleware->add('GET', '/foo', 'handler');
        $serverRequest = new ServerRequest('GET', '/foo');
        $response = $this->createMock(ResponseInterface::class);

        /** @var RequestHandlerInterface $requestHandler */
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->once())->method('handle')->willReturn($response);

        $result = $routerMiddleware->process($serverRequest, $requestHandler);
        $this->assertEquals($result, $response);
    }

    /**
     * @expectedException \ConstanzeStandard\Fluff\Exception\HttpMethodNotAllowedException
     */
    public function testProcessWithHttpMethodNotAllowedException()
    {
        $routerMiddleware = new RouterMiddleware();
        $routerMiddleware->add('GET', '/foo', 'handler');
        $serverRequest = new ServerRequest('POST', '/foo');
        $response = $this->createMock(ResponseInterface::class);

        /** @var RequestHandlerInterface $requestHandler */
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $routerMiddleware->process($serverRequest, $requestHandler);
    }

    /**
     * @expectedException \ConstanzeStandard\Fluff\Exception\HttpNotFoundException
     */
    public function testProcessWithHttpNotFoundException()
    {
        $routerMiddleware = new RouterMiddleware();
        $routerMiddleware->add('GET', '/foo/bar', 'handler');
        $serverRequest = new ServerRequest('GET', '/foo');

        /** @var RequestHandlerInterface $requestHandler */
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $routerMiddleware->process($serverRequest, $requestHandler);
    }

    public function testAttachRouteCollection()
    {
        $routerMiddleware = new RouterMiddleware();
        $routeGroup1 = $this->createMock(RouteGroup::class);
        $routeGroup2 = $this->createMock(RouteGroup::class);
        $routeGroup1->expects($this->once())->method('getRoutes')->willReturn([]);
        $routeGroup2->expects($this->once())->method('getRoutes')->willReturn([]);

        $this->setProperty($routerMiddleware, 'routeGroups', [$routeGroup1, $routeGroup2]);

        $store = new \SplObjectStorage();
        $store[$routeGroup1] = function(RouteGroup $routeGroup) use ($routeGroup1) {
            $this->assertEquals($routeGroup, $routeGroup1);
        };
        $store[$routeGroup2] = function(RouteGroup $routeGroup)  use ($routeGroup2) {
            $this->assertEquals($routeGroup, $routeGroup2);
        };
        $this->setProperty($routerMiddleware, 'groupHandlers', $store);
        $this->callMethod($routerMiddleware, 'attachRouteCollection');
    }

    public function testCollectionToRoutes()
    {
        $collection = new RouteCollection();
        $collection->add('GET', '/foo', 'serializable', 'unserializable');
        $routerMiddleware = new RouterMiddleware();

        $routes = $this->callMethod($routerMiddleware, 'collectionToRoutes', [$collection]);
        $this->assertCount(1, $routes);
        $this->assertInstanceOf(Route::class, $routes[0]);
    }
}
