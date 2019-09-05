<?php

use Beige\Invoker\Interfaces\InvokerInterface;
use Beige\Invoker\Invoker;
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Interfaces\HttpRouterInterface;
use ConstanzeStandard\Route\Dispatcher;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/AbstractTest.php';

class ApplicationTest extends AbstractTest
{
    public function testWithMiddleware()
    {
        $app = new Application();
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $app->withMiddleware($middleware);
        $outerMiddlewares = $this->getProperty($app, 'outerMiddlewares');
        $this->assertEquals([$middleware], $outerMiddlewares);
    }

    public function testGetInvokerWithProperty()
    {
        $app = new Application();
        /** @var InvokerInterface $middleware */
        $invoker = $this->createMock(InvokerInterface::class);
        $this->setProperty($app, 'invoker', $invoker);
        $result = $app->getInvoker();
        $this->assertEquals($result, $invoker);
    }

    public function testGetInvokerWithoutProperty()
    {
        $app = new Application();
        $result = $app->getInvoker();
        $this->assertInstanceOf(Invoker::class, $result);
    }

    public function testTypehintHandlers()
    {
        $app = new Application();
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $this->setProperty($app, 'typehintHandlers', [
            ServerRequestInterface::class => $mockRequest
        ]);
        $invoker = $app->getInvoker();
        $invoker->call(function (ServerRequestInterface $request) use ($mockRequest) {
            $this->assertInstanceOf(ServerRequestInterface::class, $request);
            $this->assertEquals($mockRequest, $request);
        });
    }

    /**
     * @expectedException \Exception
     */
    public function testTypehintHandlersThrowException()
    {
        $app = new Application();
        $invoker = $app->getInvoker();
        $invoker->call(function (Nothing $nothing) {});
    }

    public function testInvokeCallable()
    {
        $app = new Application();
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $mockResponse */
        $mockResponse = $this->createMock(ResponseInterface::class);
        $result = $app->__invoke($mockRequest, function() use ($mockResponse) {
            return $mockResponse;
        });
        $this->assertEquals($mockResponse, $result);
    }

    public function testInvokeArray()
    {
        $app = new Application();
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $mockResponse */
        $mockResponse = $this->createMock(ResponseInterface::class);

        $cls = new class {
            public function index() {
                $response = new Response();
                return $response;
            }
        };
        $result = $app->__invoke($mockRequest, [get_class($cls), 'index']);
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvokeException()
    {
        $app = new Application();
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $result = $app->__invoke($mockRequest, 'nothing');
    }

    public function testStart()
    {
        $app = new Application();
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface $mockResponse */
        $mockResponse = $this->createMock(ResponseInterface::class);
        $handler = function() use ($mockResponse) {
            return $mockResponse;
        };
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $httpRouter->expects($this->once())->method('dispatch')
            ->willReturn([
                Dispatcher::STATUS_OK, $handler, [], []
            ]);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $result = $app->start($mockRequest);
        $this->assertEquals($mockResponse, $result);
    }

    /**
     * @expectedException \ConstanzeStandard\Fluff\Exception\MethodNotAllowedException
     */
    public function testDispachRequestOrThrowMethodNotAllowedException()
    {
        $app = new Application();
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $httpRouter->expects($this->once())->method('dispatch')
            ->willReturn([
                Dispatcher::STATUS_ERROR, Dispatcher::ERROR_METHOD_NOT_ALLOWED, ['GET']
            ]);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $this->callMethod($app, 'dispachRequestOrThrow', [$mockRequest]);
    }

    /**
     * @expectedException \ConstanzeStandard\Fluff\Exception\NotFoundException
     */
    public function testDispachRequestOrThrowNotFoundException()
    {
        $app = new Application();
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $httpRouter->expects($this->once())->method('dispatch')
            ->willReturn([
                Dispatcher::STATUS_ERROR, Dispatcher::ERROR_NOT_FOUND
            ]);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $this->callMethod($app, 'dispachRequestOrThrow', [$mockRequest]);
    }
}
