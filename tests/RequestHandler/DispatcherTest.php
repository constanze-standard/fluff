<?php

use ConstanzeStandard\Fluff\Interfaces\RouterInterface;
use ConstanzeStandard\Fluff\RequestHandler\Args;
use ConstanzeStandard\Fluff\Routing\DispatchInformation;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use ConstanzeStandard\Fluff\RequestHandler\Dispatcher;
use ConstanzeStandard\Standard\Http\Server\DispatchInformationInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/../AbstractTest.php';

class DispatcherTest extends AbstractTest
{
    public function testGetRouter()
    {
        $dispatcher = new Dispatcher(Args::getDefinition());
        $router = $dispatcher->getRouter();
        $this->assertInstanceOf(RouterInterface::class, $router);
    }

    public function testHandle()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())->method('process')->willReturn($response);
        /** @var RouterInterface $router */
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())->method('matchOrFail')->with($request)->willReturn([
            ['middlewares' => [$middleware]], function() use ($response) {
                return $response;
            }, []
        ]);

        $dispatcher = new Dispatcher(Args::getDefinition(), $router);
        $result = $dispatcher->handle($request);
        $this->assertEquals($response, $result);
    }
}
