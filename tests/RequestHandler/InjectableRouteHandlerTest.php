<?php

use Beige\Invoker\Interfaces\InvokerInterface;
use Beige\Psr11\Container;
use ConstanzeStandard\Fluff\RequestHandler\InjectableRouteHandler;
use ConstanzeStandard\Fluff\RequestHandler\RouteHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class Controller
{
    public function __invoke()
    {
        return new Response();
    }

    public function index()
    {
        return $this->__invoke();
    }
}

class InjectableRouteHandlerTest extends AbstractTest
{
    public function testGetInvoker()
    {
        $container = new Container();
        $handler = new InjectableRouteHandler($container, 'route');
        $result = $handler->getInvoker();
        $this->assertInstanceOf(InvokerInterface::class, $result);
    }

    public function testHandleWithCallable()
    {
        $response = new Response();
        $func = function() use ($response) {
            return $response;
        };
        $container = new Container();
        $handler = new InjectableRouteHandler($container, 'route');
        $class = $this->callMethod($handler, 'getRequestHandler', [
            $func, []
        ]);
        $this->assertInstanceOf(RequestHandlerInterface::class, $class);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $result = $class->handle($request);
        $this->assertEquals($result, $response);
    }

    public function testHandleWithArray()
    {
        $container = new Container();
        $handler = new InjectableRouteHandler($container, 'route');
        $class = $this->callMethod($handler, 'getRequestHandler', [
            [Controller::class, 'index'], []
        ]);
        $this->assertInstanceOf(RequestHandlerInterface::class, $class);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $result = $class->handle($request);
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandleWithArrayInvoke()
    {
        $container = new Container();
        $handler = new InjectableRouteHandler($container, 'route');
        $class = $this->callMethod($handler, 'getRequestHandler', [
            [Controller::class], []
        ]);
        $this->assertInstanceOf(RequestHandlerInterface::class, $class);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $result = $class->handle($request);
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHandleWithException()
    {
        $container = new Container();
        $handler = new InjectableRouteHandler($container, 'route');
        $class = $this->callMethod($handler, 'getRequestHandler', [
            'NotCallable', []
        ]);
        $this->assertInstanceOf(RequestHandlerInterface::class, $class);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $class->handle($request);
    }
}
