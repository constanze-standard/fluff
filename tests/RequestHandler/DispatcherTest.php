<?php

use ConstanzeStandard\Fluff\Component\DispatchInformation;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use ConstanzeStandard\Fluff\RequestHandler\Dispatcher;
use ConstanzeStandard\Standard\Http\Server\DispatchInformationInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/../AbstractTest.php';

class DispatcherTest extends AbstractTest
{
    public function testHandle()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();
        $mockHandler = function(ServerRequestInterface $request, $args) use ($response) {
            return $response;
        };

        $dispatchInformation = new DispatchInformation($mockHandler, [], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with(DispatchInformationInterface::class)
            ->willReturn($dispatchInformation);
        $handler = new Dispatcher(Handler::getDefinition());
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

        $dispatchInformation = new DispatchInformation($mockHandler, [$middleware], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with(DispatchInformationInterface::class)
            ->willReturn($dispatchInformation);
        $handler = new Dispatcher(Handler::getDefinition());
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
        $dispatchInformation = new DispatchInformation($mockHandler, [], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with(DispatchInformationInterface::class)
            ->willReturn($dispatchInformation);
        $handler = new Dispatcher(Handler::getDefinition());
        $handler->handle($request);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAbstractDispatcherHandleRuntimeException()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with(DispatchInformationInterface::class)
            ->willReturn(null);
        $handler = new Dispatcher(Handler::getDefinition());
        $handler->handle($request);
    }
}
