<?php

use ConstanzeStandard\Fluff\Interfaces\RouterInterface;
use ConstanzeStandard\Fluff\RequestHandler\Args;
use ConstanzeStandard\Fluff\RequestHandler\Dispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/../AbstractTest.php';

class DispatcherTest extends AbstractTest
{
    public function testGetRouter()
    {
        $dispatcher = new Dispatcher(Args::getDefinition());
        $this->assertInstanceOf(RouterInterface::class, $dispatcher);
    }

    public function testHandle()
    {
        /** @var MockObject|UriInterface $uri */
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())->method('getPath')->willReturn('');
        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getUri')->willReturn($uri);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var MockObject|MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())->method('process')->willReturn($response);

        $dispatcher = new Dispatcher(Args::getDefinition());
        $dispatcher->addMiddleware($middleware);
        $dispatcher->get('', function() {});
        $result = $dispatcher->handle($request);
        $this->assertEquals($response, $result);
    }
}
