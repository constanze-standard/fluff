<?php

use ConstanzeStandard\Fluff\Component\Route;
use ConstanzeStandard\Fluff\Component\RouteGroup;
use ConstanzeStandard\Fluff\Component\RouteService;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Routing\RouteCollection;
use ConstanzeStandard\Standard\Http\Server\DispatchInformationInterface;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouterMiddlewareTest extends AbstractTest
{
    public function testGetRouteService()
    {
        $routerMiddleware = new RouterMiddleware();
        $result = $routerMiddleware->getRouteService();
        $this->assertInstanceOf(RouteService::class, $result);
    }

    public function testAddMiddleware()
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $routeGroup = $this->createMock(RouteGroup::class);
        $routeGroup->expects($this->once())->method('addMiddleware')->willReturn($middleware);
        $routerMiddleware = new RouterMiddleware();
        $this->setProperty($routerMiddleware, 'group', $routeGroup);
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
        $this->setProperty($routerMiddleware, 'group', $routeGroup);
        $result = $routerMiddleware->addRoute($route);
        $this->assertEquals($route, $result);
    }

    public function testAdd()
    {
        /** @var Route $route */
        $route = $this->createMock(Route::class);
        $routeGroup = $this->createMock(RouteGroup::class);
        $routeGroup->expects($this->once())->method('add')
            ->with('GET', '/foo', 'handler', [], null)
            ->willReturn($route);
        $routerMiddleware = new RouterMiddleware();
        $this->setProperty($routerMiddleware, 'group', $routeGroup);
        $result = $routerMiddleware->add('GET', '/foo', 'handler');
        $this->assertEquals($route, $result);
    }

    public function testGroup()
    {
        /** @var MiddlewareInterface $middleware1 */
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        /** @var MiddlewareInterface $middleware2 */
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $func = function() {};
        $routeGroup = $this->createMock(RouteGroup::class);
        $routerMiddleware = new RouterMiddleware();
        $routerMiddleware->addMiddleware($middleware2);
        $this->setProperty($routerMiddleware, 'group', $routeGroup);
        $result = $routerMiddleware->group($func, '/foo', [$middleware1]);
        $this->assertInstanceOf(RouteGroup::class, $result);
        $prefix = $this->getProperty($result, 'prefix');
        $this->assertEquals($prefix, '/foo');
        $middlewares = $this->getProperty($result, 'middlewares');
        $this->assertEquals($middlewares, [$middleware1, $middleware2]);
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
