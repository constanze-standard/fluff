<?php

use ConstanzeStandard\Fluff\Component\RouteData;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use ConstanzeStandard\Fluff\RequestHandler\RouteHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouteHandlerTest extends AbstractTest
{
    public function testHandle()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();
        $mockHandler = function(ServerRequestInterface $request, $args) use ($response) {
            return $response;
        };

        $routeData = new RouteData($mockHandler, [], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with('route')
            ->willReturn($routeData);
        $handler = new RouteHandler(Handler::getDefinition(), 'route');
        $result = $handler->handle($request);
        $this->assertEquals($result, $response);
    }

    public function testHandleWithMiddlewares()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();
        $mockHandler = function(ServerRequestInterface $request, $args) use ($response) {
            return $response;
        };

        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->exactly(1))->method('process')->willReturn($response);

        $routeData = new RouteData($mockHandler, [$middleware], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with('route')
            ->willReturn($routeData);
        $handler = new RouteHandler(Handler::getDefinition(), 'route');
        $result = $handler->handle($request);
        $this->assertEquals($result, $response);
    }

    /**
     * @expectedException \TypeError
     */
    public function testHandleNotCallable()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $mockHandler = 'NotCallable';
        $routeData = new RouteData($mockHandler, [], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with('route')
            ->willReturn($routeData);
        $handler = new RouteHandler(Handler::getDefinition(), 'route');
        $handler->handle($request);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAbstractRouteHandlerHandleRuntimeException()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();
        $mockHandler = 'NotCallable';
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with('route')
            ->willReturn(null);
        $handler = new RouteHandler(Handler::getDefinition(), 'route');
        $handler->handle($request);
    }
}
