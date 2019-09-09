<?php

use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\RequestHandler\Interfaces\MiddlewareDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/AbstractTest.php';

class ApplicationTest extends AbstractTest
{
    public function testAddMiddleware()
    {
        /** @var RequestHandlerInterface $requestHandler */
        /** @var MiddlewareInterface $middleware */

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middlewareDispatcher = $this->createMock(MiddlewareDispatcherInterface::class);
        $middlewareDispatcher->expects($this->once())->method('addMiddleware')->willReturn($middleware);
        $app = new Application($requestHandler);
        $this->setProperty($app, 'middlewareDispatcher', $middlewareDispatcher);
        $app->addMiddleware($middleware);
    }

    public function testHandle()
    {
        /** @var RequestHandlerInterface $requestHandler */
        /** @var MiddlewareInterface $middleware */
        /** @var ServerRequestInterface $request */

        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('HEAD');
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())->method('isWritable')->willReturn(true);
        $stream->expects($this->once())->method('isSeekable')->willReturn(true);
        $stream->expects($this->once())->method('rewind');
        $stream->expects($this->once())->method('write');
        $request->expects($this->once())->method('getBody')->willReturn($stream);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $middlewareDispatcher = $this->createMock(MiddlewareDispatcherInterface::class);
        $middlewareDispatcher->expects($this->once())->method('handle')->with($request)->willReturn($response);
        $app = new Application($requestHandler);
        $this->setProperty($app, 'middlewareDispatcher', $middlewareDispatcher);
        $result = $app->handle($request);
        $this->assertEquals($response, $result);
    }
}
