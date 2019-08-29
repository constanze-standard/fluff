<?php

use Beige\Invoker\Interfaces\InvokerInterface;
use Beige\Psr11\Container;
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Conponent\HttpRouter;
use ConstanzeStandard\Fluff\Interfaces\HttpRouterInterface;
use ConstanzeStandard\Fluff\InvokerProxy;
use ConstanzeStandard\Route\Dispatcher;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/AbstractTest.php';

class ApplicationTest extends AbstractTest
{
    public function testGetContainer()
    {
        $app = new Application();
        $container = $this->createMock(ContainerInterface::class);
        $this->setProperty($app, 'container', $container);
        $result = $app->getContainer();
        $this->assertEquals($result, $container);
    }

    public function testGetSettings()
    {
        $app = new Application();
        $this->setProperty($app, 'settings', ['foo' => 'bar']);
        $result = $app->getSettings();
        $this->assertEquals($result, ['foo' => 'bar']);
    }

    public function testGetInvokerProxy()
    {
        $app = new Application();
        $result = $app->getInvokerProxy();
        $this->assertInstanceOf(InvokerProxy::class, $result);
        $invokerProxy = $this->getProperty($app, 'invokerProxy');
        $this->assertEquals($invokerProxy, $result);

        $result = $app->getInvokerProxy();
        $this->assertInstanceOf(InvokerProxy::class, $result);
    }

    public function testGetHttpRouterWithProperty()
    {
        $app = new Application();
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $result = $app->getHttpRouter();
        $this->assertEquals($httpRouter, $result);
    }

    public function testGetHttpRouterWithContainer()
    {
        $app = new Application();
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->willReturn(true);
        $container->expects($this->once())->method('get')->willReturn($httpRouter);
        $this->setProperty($app, 'container', $container);
        $result = $app->getHttpRouter();
        $this->assertEquals($httpRouter, $result);
    }

    public function testGetHttpRouterWithNothing()
    {
        $app = new Application();
        $result = $app->getHttpRouter();
        $this->assertInstanceOf(HttpRouter::class, $result);
    }

    public function testWithRoute()
    {
        $app = new Application();
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $arguments = ['get', '/user', 'controller', []];
        $httpRouter->expects($this->once())->method('withRoute')->with(...$arguments);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $app->withRoute(...$arguments);
    }

    public function testWithGroupWithoutName()
    {
        $app = new Application();
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $arguments = ['/user', [], is_string::class];
        $httpRouter->expects($this->once())->method('withGroup')->with(...$arguments);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $app->withGroup(...$arguments);
    }

    public function testWithGroupWithName()
    {
        $app = new Application();
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $arguments = ['/user', ['name' => 'alex'], is_string::class];
        $httpRouter->expects($this->once())->method('withGroup')->with('/user', [], is_string::class);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $app->withGroup(...$arguments);
    }

    public function testWithMiddleware()
    {
        $app = new Application();
        $app->withMiddleware('middleware');
        $middlewares = $this->getProperty($app, 'middlewares');
        $this->assertEquals($middlewares, ['middleware']);
    }

    public function testWithFilter()
    {
        $app = new Application();
        $app->withFilter('key', is_string::class);
        $filtersMap = $this->getProperty($app, 'filtersMap');
        $this->assertEquals($filtersMap, ['key' => is_string::class]);
    }

    public function testWithExceptionHandler()
    {
        $app = new Application();
        $app->withExceptionHandler('key', is_string::class);
        $exceptionHandlers = $this->getProperty($app, 'exceptionHandlers');
        $this->assertEquals($exceptionHandlers, ['key' => is_string::class]);
    }

    public function testInvokeWithCallable()
    {
        $callable = function($foo) {
            return $foo;
        };
        $app = new Application();
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())->method('call')->with($callable, ['foo' => 'bar'])->willReturn('bar');
        $this->setProperty($app, 'invoker', $invoker);
        $result = $app($callable, ['foo' => 'bar']);
        $this->assertEquals($result, 'bar');
    }

    public function testInvokeWithArray()
    {
        $obj = new \stdClass();
        $app = new Application();
        $controller = ['foo', 'bar'];
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())->method('new')->with($controller[0])->willReturn($obj);
        $invoker->expects($this->once())->method('callMethod')->willReturn(1);
        $this->setProperty($app, 'invoker', $invoker);
        $result = $app($controller, ['foo' => 'bar']);
        $this->assertEquals($result, 1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvokeWithException()
    {
        $app = new Application();
        $app('foo', []);
    }

    public function testStartWithFound()
    {
        $app = new Application();
        $response = new Response();
        $controller = function() use ($response) {
            return $response;
        };
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $httpRouter->expects($this->once())
            ->method('dispatch')->with($request)
            ->willReturn([Dispatcher::STATUS_OK, $controller, [], []]);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $app->start($request);
        ob_start();
    }

    /**
     * @expectedException \ConstanzeStandard\Fluff\Exception\MethodNotAllowedException
     */
    public function testStartWithMethodNotAllowed()
    {
        $app = new Application();
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $httpRouter->expects($this->once())
            ->method('dispatch')->with($request)
            ->willReturn([Dispatcher::STATUS_ERROR, Dispatcher::ERROR_METHOD_NOT_ALLOWED, ['get']]);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $app->start($request);
        ob_start();
    }

    /**
     * @expectedException \ConstanzeStandard\Fluff\Exception\NotFoundException
     */
    public function testStartWithNotFound()
    {
        $app = new Application();
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $httpRouter = $this->createMock(HttpRouterInterface::class);
        $httpRouter->expects($this->once())
            ->method('dispatch')->with($request)
            ->willReturn([Dispatcher::STATUS_ERROR, Dispatcher::ERROR_NOT_FOUND]);
        $this->setProperty($app, 'httpRouter', $httpRouter);
        $app->start($request);
        ob_start();
    }

    public function testOutputResponseClean()
    {
        $app = new Application();
        $response = new Response(200, [], 'data');
        $app->outputResponse($response, false);
        $this->assertEquals(ob_get_level(), 0);
        ob_start();
    }

    public function testOutputResponseFlush()
    {
        $app = new Application();
        $response = new Response(200, [], ' ');
        $app->outputResponse($response);
        $this->assertEquals(ob_get_level(), 0);
        ob_start();
    }

    public function testVerifyFiltersWithMap()
    {
        $app = new Application();
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $this->setProperty($app, 'filtersMap', [
            'test' => function() {return true;}
        ]);
        $filters = [
            'test' => []
        ];
        $result = $this->callMethod($app, 'verifyFilters', [$request, $filters, []]);
        $this->assertTrue($result);
    }

    public function testVerifyFiltersWithCallableFalse()
    {
        $app = new Application();
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $filters = [
            function() {return false;}
        ];
        $result = $this->callMethod($app, 'verifyFilters', [$request, $filters, []]);
        $this->assertFalse($result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRespondHeader()
    {
        $app = new Application();
        $response = new Response(200, ['Content-Type' => 'text/plain']);
        $this->callMethod($app, 'respondHeader', [$response]);
        $this->assertFalse(headers_sent());
    }

    public function testInvokerDefaultHandler()
    {
        $app = new Application();
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $this->setProperty($app, 'typehintHandlers', [
            ServerRequestInterface::class => $mockRequest
        ]);
        $invoker = $this->callMethod($app, 'getInvoker');
        $invoker->call(function (ServerRequestInterface $request) use ($mockRequest) {
            $this->assertInstanceOf(ServerRequestInterface::class, $request);
            $this->assertEquals($mockRequest, $request);
        });
    }

    /**
     * @expectedException \Exception
     */
    public function testInvokerDefaultHandlerThrowException()
    {
        $app = new Application();
        $invoker = $this->callMethod($app, 'getInvoker');
        $invoker->call(function (Nothing $nothing) {});
    }

    public function testExceptionHandlerProcess()
    {
        $app = new Application();
        $response = new Response();
        $request = $this->createMock(ServerRequestInterface::class);
        $this->setProperty($app, 'exceptionHandlers', [
            \RuntimeException::class => function () use ($response) {
                return $response;
            }
        ]);
        $e = new \RuntimeException();
        $result = $this->callMethod($app, 'exceptionHandlerProcess', [$request, $e]);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals($result, $response);
    }

    public function testExceptionHandlerProcessSubClasses()
    {
        $app = new Application();
        $response = new Response();
        $request = $this->createMock(ServerRequestInterface::class);
        $this->setProperty($app, 'exceptionHandlers', [
            \Exception::class => function () use ($response) {
                return $response;
            }
        ]);
        $e = new \RuntimeException();
        $result = $this->callMethod($app, 'exceptionHandlerProcess', [$request, $e]);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals($result, $response);
    }

    /**
     * @expectedException \TypeError
     */
    public function testGetRequestHandlerStackWithoutResponseReturned()
    {
        $app = new Application();
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $controller = function() {
            return null;
        };
        /** @var RequestHandlerInterface $stack */
        $stack = $this->callMethod($app, 'getRequestHandlerStack', [$controller, [], []]);
        $stack->handle($request);
    }

    public function testGetRequestHandlerStackWithMiddlewares()
    {
        $app = new Application();
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();

        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())->method('new')->with('MiddlewareName')->willReturn($middleware);
        $this->setProperty($app, 'invoker', $invoker);
        $controller = function() use ($response) {
            return $response;
        };
        /** @var RequestHandlerInterface $stack */
        $stack = $this->callMethod($app, 'getRequestHandlerStack', [$controller, [
            'MiddlewareName'
        ], []]);
        $this->assertInstanceOf(RequestHandlerInterface::class, $stack);
    }

    public function testEndOutputBuffersWithFastcgi()
    {
        if (!\function_exists('fastcgi_finish_request')) {
            function fastcgi_finish_request() {
                return true;
            }
        }
        $app = new Application();
        $result = $this->callMethod($app, 'endOutputBuffers', [true]);
        $this->assertTrue($result);
    }
}
