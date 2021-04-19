<?php

use ConstanzeStandard\Fluff\Exception\HttpMethodNotAllowedException;
use ConstanzeStandard\Fluff\Exception\HttpNotFoundException;
use ConstanzeStandard\Fluff\Interfaces\RouteGroupInterface;
use ConstanzeStandard\Fluff\Interfaces\RouteServiceInterface;
use ConstanzeStandard\Fluff\Routing\Router;
use ConstanzeStandard\Routing\Interfaces\RouteCollectionInterface;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouterTest extends AbstractTest
{
    public function testGetRouteCollection()
    {
        $router = new Router();
        $result = $router->getRouteCollection();
        $this->assertInstanceOf(RouteCollectionInterface::class, $result);
    }

    public function testGetRouteService()
    {
        $router = new Router();
        $result = $router->getRouteService();
        $this->assertInstanceOf(RouteServiceInterface::class, $result);
    }

    public function testGroup()
    {
        $router = new Router();
        $result = $router->group(function() {});
        $this->assertInstanceOf(RouteGroupInterface::class, $result);
    }

    public function testMatchOrFailMatched()
    {
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);

        $router = new Router();
        $h1 = function() use ($response) {
            return $response;
        };
        $router->add('GET', '/a', $h1, [$middleware]);

        $router->add('GET', '/b', function() use ($response) {
            return $response;
        });

        $request = new ServerRequest('GET', '/a');
        $result = $router->matchOrFail($request);
        $this->assertEquals($result, [
            ['middlewares' => [$middleware], 'name' => null], $h1, []
        ]);
    }

    public function testMatchOrFailWithHttpMethodNotAllowedException()
    {
        $this->expectException(HttpMethodNotAllowedException::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $router = new Router();
        $h1 = function() use ($response) {
            return $response;
        };
        $router->add('GET', '/a', $h1);

        $router->add('GET', '/b', function() use ($response) {
            return $response;
        });

        $request = new ServerRequest('POST', '/a');
        $router->matchOrFail($request);
    }

    public function testMatchOrFailWithHttpNotFoundException()
    {
        $this->expectException(HttpNotFoundException::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $router = new Router();
        $router->group(function($r) use ($response) {
            $r->add('GET', '/b', function() use ($response) {
                return $response;
            });
        });

        $request = new ServerRequest('GET', '/c');
        $router->matchOrFail($request);
    }
}
