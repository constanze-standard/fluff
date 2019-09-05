<?php

use Beige\Psr11\Container;
use ConstanzeStandard\Fluff\Component\HttpRouter;
use ConstanzeStandard\Fluff\Interfaces\HttpRouterInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteParserInterface;
use ConstanzeStandard\Fluff\RouterProxy;
use ConstanzeStandard\Fluff\Service\RouteParser;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use ConstanzeStandard\Route\Interfaces\DispatcherInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/AbstractTest.php';

class RouterProxyTest extends AbstractTest
{
    private function makeStubs()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $routerProxy = new RouterProxy($container);
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $this->setProperty($routerProxy, 'httpRouter', $httpRouter);
        return [$container, $routerProxy, $httpRouter];
    }

    public function testGetHttpRouterWithProperty()
    {
        list($container, $routerProxy, $httpRouter) = $this->makeStubs();
        $result = $routerProxy->getHttpRouter();
        $this->assertEquals($result, $httpRouter);
    }

    public function testGetHttpRouterWithoutProperty()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $routerProxy = new RouterProxy($container);
        $result = $routerProxy->getHttpRouter();
        $this->assertInstanceOf(HttpRouter::class, $result);
    }

    public function testGetRouteDispatcher()
    {
        $collection = $this->createMock(CollectionInterface::class);
        $dispatcher = $this->createMock(DispatcherInterface::class);
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(1))->method('has')->with(DispatcherInterface::class)->willReturn(true);
        $container->expects($this->exactly(1))->method('get')
            ->with(DispatcherInterface::class)
            ->willReturn($dispatcher);
        $routerProxy = new RouterProxy($container);
        $this->setProperty($routerProxy, 'container', $container);
        $result = $this->callMethod($routerProxy, 'getRouteDispatcher');
        $this->assertInstanceOf(DispatcherInterface::class, $result);
    }

    public function testGetRouteCollection()
    {
        $collection = $this->createMock(CollectionInterface::class);
        $dispatcher = $this->createMock(DispatcherInterface::class);
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(1))->method('has')->with(CollectionInterface::class)->willReturn(true);
        $container->expects($this->exactly(1))->method('get')
            ->with(CollectionInterface::class)
            ->willReturn($collection);
        $routerProxy = new RouterProxy(new Container(['settings' => []]));
        $this->setProperty($routerProxy, 'container', $container);
        $result = $this->callMethod($routerProxy, 'getRouteCollection');
        $this->assertInstanceOf(CollectionInterface::class, $result);
    }

    public function testVerifyFiltersPass()
    {
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $routerProxy = new RouterProxy(new Container(['settings' => []]));
        $this->setProperty($routerProxy, 'filtersMap', [
            'test' => function($serverRequest, $option, $params) {
                return true;
            }
        ]);
        $result = $this->callMethod($routerProxy, 'verifyFilters', [$mockRequest, ['test' => 'option'], []]);
        $this->assertTrue($result);
    }

    public function testVerifyFiltersNoPass()
    {
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $routerProxy = new RouterProxy(new Container(['settings' => []]));
        $this->setProperty($routerProxy, 'filtersMap', [
            'test' => function($serverRequest, $option, $params) {
                return false;
            }
        ]);
        $result = $this->callMethod($routerProxy, 'verifyFilters', [$mockRequest, ['test' => 'option'], []]);
        $this->assertFalse($result);
    }

    public function testVerifyFiltersCallable()
    {
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $routerProxy = new RouterProxy(new Container(['settings' => []]));
        $this->setProperty($routerProxy, 'filtersMap', []);
        $result = $this->callMethod($routerProxy, 'verifyFilters', [$mockRequest, ['test' => function($serverRequest, $params) {
            return true;
        }], []]);
        $this->assertTrue($result);
    }

    public function testWithFilter()
    {
        $routerProxy = new RouterProxy(new Container());
        $callable = function() {};
        $routerProxy->withFilter('test', $callable);
        $result = $this->getProperty($routerProxy, 'filtersMap');
        $this->assertEquals($result, ['test' => $callable]);
    }

    public function testWithRoute()
    {
        list($container, $routerProxy, $httpRouter) = $this->makeStubs();
        $httpRouter->expects($this->exactly(1))->method('withRoute');
        $routerProxy->withRoute('GET', '/user', ['a', 'b']);
    }

    public function testWithGroup()
    {
        list($container, $routerProxy, $httpRouter) = $this->makeStubs();
        $callback = function() {};
        $httpRouter->expects($this->exactly(1))
            ->method('withGroup')
            ->with('/user', ['a' => 'b'], $callback);
        $routerProxy->withGroup('/user', ['name' => 'test', 'a' => 'b'], $callback);
    }

    public function testGetRouteParserWithoutPropertyWithContainer()
    {
        list($container, $routerProxy, $httpRouter) = $this->makeStubs();
        /** @var ContainerInterface $container */
        $routeParser = $this->createMock(RouteParserInterface::class);
        $container->expects($this->exactly(1))->method('has')->with(RouteParserInterface::class)->willReturn(true);
        $container->expects($this->exactly(1))->method('get')->with(RouteParserInterface::class)->willReturn($routeParser);
        $result = $routerProxy->getRouteParser();
        $this->assertEquals($result, $routeParser);
    }

    public function testGetRouteParserWithoutPropertyWithoutContainer()
    {
        $routerProxy = new RouterProxy(new Container());
        $result = $routerProxy->getRouteParser();
        $this->assertInstanceOf(RouteParser::class, $result);
    }
}
