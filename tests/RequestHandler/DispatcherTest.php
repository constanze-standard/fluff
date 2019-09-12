<?php

use ConstanzeStandard\Fluff\Component\DispatchData;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use ConstanzeStandard\Fluff\RequestHandler\Dispatcher;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

        $dispatchData = new DispatchData($mockHandler, [], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with(DispatchData::ATTRIBUTE_NAME)
            ->willReturn($dispatchData);
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

        $dispatchData = new DispatchData($mockHandler, [$middleware], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with(DispatchData::ATTRIBUTE_NAME)
            ->willReturn($dispatchData);
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
        $dispatchData = new DispatchData($mockHandler, [], []);
        $request->expects($this->exactly(1))->method('getAttribute')
            ->with(DispatchData::ATTRIBUTE_NAME)
            ->willReturn($dispatchData);
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
            ->with(DispatchData::ATTRIBUTE_NAME)
            ->willReturn(null);
        $handler = new Dispatcher(Handler::getDefinition());
        $handler->handle($request);
    }
}
