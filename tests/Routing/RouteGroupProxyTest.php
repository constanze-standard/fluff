<?php

use ConstanzeStandard\Fluff\Interfaces\RouteGroupInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteInterface;
use ConstanzeStandard\Fluff\Routing\RouteGroupProxy;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouteGroupProxyTest extends AbstractTest
{
    public function testAddMiddleware()
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        /** @var RouteGroupInterface $routeGroup */
        $routeGroup = $this->createMock(RouteGroupInterface::class);
        $routeGroup->expects($this->once())->method('addMiddleware')->with($middleware)->willReturn($middleware);
        $routeGroupProxy = new RouteGroupProxy($routeGroup);
        $result = $routeGroupProxy->addMiddleware($middleware);
        $this->assertEquals($result, $middleware);
    }

    public function testAddRoute()
    {
        /** @var RouteInterface $route */
        $route = $this->createMock(RouteInterface::class);
        /** @var RouteGroupInterface $routeGroup */
        $routeGroup = $this->createMock(RouteGroupInterface::class);
        $routeGroup->expects($this->once())->method('addRoute')->with($route)->willReturn($route);
        $routeGroupProxy = new RouteGroupProxy($routeGroup);
        $result = $routeGroupProxy->addRoute($route);
        $this->assertEquals($result, $route);
    }

    public function testAdd()
    {
        /** @var RouteInterface $route */
        $route = $this->createMock(RouteInterface::class);
        /** @var RouteGroupInterface $routeGroup */
        $routeGroup = $this->createMock(RouteGroupInterface::class);
        $routeGroup->expects($this->once())->method('add')->with('GET', '/foo', 'HANDLER', [], 'name')->willReturn($route);
        $routeGroupProxy = new RouteGroupProxy($routeGroup);
        $result = $routeGroupProxy->add('GET', '/foo', 'HANDLER', [], 'name');
        $this->assertEquals($result, $route);
    }

    public function testDeriveGroup()
    {
        /** @var RouteGroupInterface $routeGroup */
        $routeGroup = $this->createMock(RouteGroupInterface::class);
        /** @var RouteGroupInterface $routeGroup */
        $routeGroup = $this->createMock(RouteGroupInterface::class);
        $routeGroup->expects($this->once())->method('derive')->with('', [])->willReturn($routeGroup);
        $routeGroupProxy = new RouteGroupProxy($routeGroup);
        $result = $routeGroupProxy->deriveGroup('', []);
        $this->assertEquals($result, $routeGroup);
    }

    public function testSetPrefix()
    {
        /** @var RouteGroupInterface $routeGroup */
        $routeGroup = $this->createMock(RouteGroupInterface::class);
        $routeGroup->expects($this->once())->method('setPrefix')->with('');
        $routeGroupProxy = new RouteGroupProxy($routeGroup);
        $result = $routeGroupProxy->setPrefix('');
        $this->assertEquals($result, $routeGroupProxy);
    }

    public function testGetRootGroup()
    {
        /** @var RouteGroupInterface $routeGroup */
        $routeGroup = $this->createMock(RouteGroupInterface::class);
        $routeGroupProxy = new RouteGroupProxy($routeGroup);
        $result = $routeGroupProxy->getRootGroup();
        $this->assertEquals($result, $routeGroup);
    }
}
