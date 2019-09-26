<?php

use ConstanzeStandard\Fluff\Component\Route;
use ConstanzeStandard\Fluff\Component\RouteService;

require_once __DIR__ . '/../AbstractTest.php';

class RouteServiceTest extends AbstractTest
{
    public function testGetRoutes()
    {
        /**
         * @var Route $route1
         * @var Route $route2
         * @var Route $route3
         */
        $route1 = $this->createMock(Route::class);
        $route2 = $this->createMock(Route::class);
        $route3 = $this->createMock(Route::class);
        $routeService = new RouteService([$route1, $route2]);
        $routeService->addRoute($route3);

        $routes = $routeService->getRoutes();
        $this->assertEquals($routes, [$route1, $route2, $route3]);
    }

    public function testGetRouteByName()
    {
        /**
         * @var Route $route1
         * @var Route $route2
         * @var Route $route3
         */
        $route1 = $this->createMock(Route::class);
        $route2 = $this->createMock(Route::class);
        $route3 = $this->createMock(Route::class);
        $route2->expects($this->once())->method('getName')->willReturn('r2');
        $routeService = new RouteService([$route1, $route2, $route3]);
        $route = $routeService->getRouteByName('r2');
        $this->assertEquals($route, $route2);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetRouteByNameWithException()
    {
        /**
         * @var Route $route1
         * @var Route $route2
         * @var Route $route3
         */
        $route1 = $this->createMock(Route::class);
        $route2 = $this->createMock(Route::class);
        $route3 = $this->createMock(Route::class);
        $route1->expects($this->once())->method('getName')->willReturn('r1');
        $route2->expects($this->once())->method('getName')->willReturn('r2');
        $route3->expects($this->once())->method('getName')->willReturn('r3');
        $routeService = new RouteService([$route1, $route2, $route3]);
        $routeService->getRouteByName('r4');
    }

    public function testGetUrlByRoute()
    {
        /**
         * @var Route $route2
         */
        $route2 = $this->createMock(Route::class);
        $route2->expects($this->once())->method('getPattern')->willReturn('/foo/{bar:\d+}');
        $routeService = new RouteService([$route2]);

        $url = $routeService->getUrlByRoute($route2, ['bar' => 20, 'nothing' => 20], ['a' => 1]);
        $this->assertEquals($url, $_SERVER['SCRIPT_NAME'] . '/foo/20?a=1');
    }

    public function testGetUrlByRouteWithNoArgumentsRegex()
    {
        /**
         * @var Route $route2
         */
        $route2 = $this->createMock(Route::class);
        $route2->expects($this->once())->method('getPattern')->willReturn('/foo/{bar}');
        $routeService = new RouteService([$route2]);

        $url = $routeService->getUrlByRoute($route2, ['bar' => 20, 'nothing' => 20], ['a' => 1]);
        $this->assertEquals($url, $_SERVER['SCRIPT_NAME'] . '/foo/20?a=1');
    }

    public function testGetUrlByRouteWithNoArguments()
    {
        /**
         * @var Route $route2
         */
        $route2 = $this->createMock(Route::class);
        $route2->expects($this->once())->method('getPattern')->willReturn('/foo/bar');
        $routeService = new RouteService([$route2]);

        $url = $routeService->getUrlByRoute($route2, ['bar' => 20, 'nothing' => 20], ['a' => 1]);
        $this->assertEquals($url, $_SERVER['SCRIPT_NAME'] . '/foo/bar?a=1');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetUrlByRouteWithErrorType()
    {
        /**
         * @var Route $route2
         */
        $route2 = $this->createMock(Route::class);
        $route2->expects($this->once())->method('getPattern')->willReturn('/foo/{bar:\d+}');
        $routeService = new RouteService([$route2]);
        $routeService->getUrlByRoute($route2, ['bar' => 'abc', 'nothing' => 20], ['a' => 1]);
    }

    public function testUrlFor()
    {
        /**
         * @var Route $route2
         */
        $route2 = $this->createMock(Route::class);
        $route2->expects($this->once())->method('getName')->willReturn('r2');
        $route2->expects($this->once())->method('getPattern')->willReturn('/foo/{bar:\d+}');
        $routeService = new RouteService([$route2]);

        $url = $routeService->urlFor('r2', ['bar' => 20, 'nothing' => 20], ['a' => 1]);
        $this->assertEquals($url, $_SERVER['SCRIPT_NAME'] . '/foo/20?a=1');
    }
}
