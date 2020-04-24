<?php

use ConstanzeStandard\Fluff\Interfaces\RouterInterface;
use ConstanzeStandard\Fluff\RequestHandler\Args;
use ConstanzeStandard\Fluff\Routing\DispatchInformation;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use ConstanzeStandard\Fluff\RequestHandler\Dispatcher;
use ConstanzeStandard\Routing\Interfaces\RouteCollectionInterface;
use ConstanzeStandard\Standard\Http\Server\DispatchInformationInterface;
use Nyholm\Psr7\Response;
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
        /** @var UriInterface $uri */
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())->method('getPath')->willReturn('');
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getUri')->willReturn($uri);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())->method('process')->willReturn($response);

        $dispatcher = new Dispatcher(Args::getDefinition());
        $dispatcher->addMiddleware($middleware);
        $dispatcher->get('', function() {});
        $result = $dispatcher->handle($request);
        $this->assertEquals($response, $result);
    }
}
