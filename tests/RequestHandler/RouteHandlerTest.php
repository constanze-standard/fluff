<?php

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

        $request->expects($this->exactly(1))->method('getAttribute')
            ->with('route')
            ->willReturn([$mockHandler, [], []]);
        $handler = new RouteHandler('route');
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

        $request->expects($this->exactly(1))->method('getAttribute')
            ->with('route')
            ->willReturn([$mockHandler, [$middleware], []]);
        $handler = new RouteHandler('route');
        $result = $handler->handle($request);
        $this->assertEquals($result, $response);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHandleNotCallable()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();
        $mockHandler = 'NotCallable';
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with('route')
            ->willReturn([$mockHandler, [], []]);
        $handler = new RouteHandler('route');
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
        $handler = new RouteHandler('route');
        $handler->handle($request);
    }
}
