<?php

use Beige\Invoker\Interfaces\InvokerInterface;
use Beige\Psr11\Container;
use ConstanzeStandard\Fluff\RequestHandler\DiHandler;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../AbstractTest.php';

class StringTest
{
    public function index()
    {
        return new Response();
    }

    public function __invoke()
    {
        return $this->index();
    }
}

class DiHandlerTest extends AbstractTest
{
    public function testHandlerIsCallable()
    {
        $response = $this->createMock(ResponseInterface::class);
        $container = new Container();
        $func = function() use ($response) {
            return $response;
        };
        $handler = new DiHandler($container, $func);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $result = $handler->handle($request);
        $this->assertEquals($response, $result);
    }

    public function testHandlerIsString()
    {
        $container = new Container();
        $handler = new DiHandler($container, 'StringTest@index');

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $result = $handler->handle($request);
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandlerIsInvoke()
    {
        $container = new Container();
        $handler = new DiHandler($container, 'StringTest');

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $result = $handler->handle($request);
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandlerIsInvokeWithContainer()
    {
        $response = $this->createMock(ResponseInterface::class);
        $container = new Container();
        $invoker = $this->createMock(InvokerInterface::class);
        $stringTest = new StringTest();
        $invoker->expects($this->once())->method('callMethod')->with($stringTest, DiHandler::DEFAULT_HANDLER_METHOD, [])->willReturn($response);
        $invoker->expects($this->once())->method('new')->with('StringTest')->willReturn($stringTest);
        $container->set(InvokerInterface::class, $invoker);
        $handler = new DiHandler($container, 'StringTest');

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $result = $handler->handle($request);
        $this->assertEquals($response, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHandlerInvalidArgumentException()
    {
        $response = $this->createMock(ResponseInterface::class);
        $container = new Container();
        $func = function() use ($response) {
            return $response;
        };
        $handler = new DiHandler($container, []);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $handler->handle($request);
    }

    public function testGetDefinition_static()
    {
        $container = new Container();
        $closure = DiHandler::getDefinition($container);
        $result = $closure('StringTest', []);
        $this->assertInstanceOf(DiHandler::class, $result);
    }
}
