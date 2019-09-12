<?php

use ConstanzeStandard\Fluff\Component\HttpRouter;
use ConstanzeStandard\Fluff\Component\DispatchData;
use ConstanzeStandard\Fluff\Component\Route;
use ConstanzeStandard\Fluff\Exception\MethodNotAllowedException;
use ConstanzeStandard\Fluff\Interfaces\RouteParserInterface;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Route\Dispatcher;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use ConstanzeStandard\Route\Interfaces\DispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouterMiddlewareTest extends AbstractTest
{
    public function testAddMiddleware()
    {
        /** @var RequestHandlerInterface $requestHandler */
        /** @var MiddlewareInterface $middleware */

        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $middleware = $this->createMock(MiddlewareInterface::class);
        $router = new RouterMiddleware($collector);
        $route = $this->createMock(Route::class);
        $route->expects($this->once())->method('addMiddleware')->with($middleware);
        $this->setProperty($router, 'routes', [$route]);
        $result = $router->addMiddleware($middleware);
        $this->assertEquals($result, $middleware);
    }

    public function testWithRoute()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $middleware = $this->createMock(MiddlewareInterface::class);
        $result = $router->withRoute('GET', '/foo', 'controller', [$middleware], 'test');
        $this->assertInstanceOf(Route::class, $result);
    }

    public function testWithGroup()
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware3 = $this->createMock(MiddlewareInterface::class);
        $router = new RouterMiddleware();
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'privMiddlewares', [$middleware1]);

        $router->withGroup('/foo', [$middleware2], function($router) use ($middleware3) {
            $router->withRoute('GET', '/bar', 'controller', [$middleware3]);
        });
        $privPrefix = $this->getProperty($router, 'privPrefix');
        $privMiddlewares = $this->getProperty($router, 'privMiddlewares');
        $this->assertEquals($privPrefix, '/prefix');
        $this->assertEquals($privMiddlewares, [$middleware1]);
    }

    public function testDispatch()
    {
        /** @var RequestHandlerInterface $requestHandler */
        /** @var ServerRequestInterface $request */
        /** @var CollectionInterface $collector */

        $dispatchData = new DispatchData('routeHandler', [1], ['id' => 1]);
        $response = $this->createMock(ResponseInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->once())->method('handle')->willReturn($response);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        $request->expects($this->once())->method('getUri')->willReturn('/foo');
        $request->expects($this->once())->method('withAttribute')
            ->with('route', $dispatchData)
            ->willReturn($request);
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->willReturn([
            Dispatcher::STATUS_OK, 'routeHandler', ['middlewares' => [1]], ['id' => 1]
        ]);
        $router = new RouterMiddleware($collector, 'route');
        $this->setProperty($router, 'dispatcher', $dispatcher);
        $result = $router->process($request, $requestHandler);
        $this->assertEquals($result, $response);
    }

    /**
     * @expectedException \ConstanzeStandard\Fluff\Exception\MethodNotAllowedException
     */
    public function testDispatchERROR_METHOD_NOT_ALLOWED()
    {
        /** @var RequestHandlerInterface $requestHandler */
        /** @var ServerRequestInterface $request */
        /** @var CollectionInterface $collector */

        $response = $this->createMock(ResponseInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->exactly(0))->method('handle')->willReturn($response);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        $request->expects($this->once())->method('getUri')->willReturn('/foo');
        $request->expects($this->exactly(0))->method('withAttribute')
            ->with('route', ['routeHandler', [1], ['id' => 1]])
            ->willReturn($request);
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->willReturn([
            Dispatcher::STATUS_ERROR, Dispatcher::ERROR_METHOD_NOT_ALLOWED, ['POST']
        ]);
        $router = new RouterMiddleware($collector, 'route');
        $this->setProperty($router, 'dispatcher', $dispatcher);
        $router->process($request, $requestHandler);
    }

    /**
     * @expectedException \ConstanzeStandard\Fluff\Exception\NotFoundException
     */
    public function testDispatchERROR_NOT_FOUND()
    {
        /** @var RequestHandlerInterface $requestHandler */
        /** @var ServerRequestInterface $request */
        /** @var CollectionInterface $collector */

        $response = $this->createMock(ResponseInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->exactly(0))->method('handle')->willReturn($response);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        $request->expects($this->once())->method('getUri')->willReturn('/foo');
        $request->expects($this->exactly(0))->method('withAttribute')
            ->with('route', ['routeHandler', [1], ['id' => 1]])
            ->willReturn($request);
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->willReturn([
            Dispatcher::STATUS_ERROR, Dispatcher::ERROR_NOT_FOUND
        ]);
        $router = new RouterMiddleware($collector, 'route');
        $this->setProperty($router, 'dispatcher', $dispatcher);
        $router->process($request, $requestHandler);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDispatchOtherError()
    {
        /** @var RequestHandlerInterface $requestHandler */
        /** @var ServerRequestInterface $request */
        /** @var CollectionInterface $collector */

        $response = $this->createMock(ResponseInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->exactly(0))->method('handle')->willReturn($response);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        $request->expects($this->once())->method('getUri')->willReturn('/foo');
        $request->expects($this->exactly(0))->method('withAttribute')
            ->with('route', ['routeHandler', [1], ['id' => 1]])
            ->willReturn($request);
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->willReturn([
            'STATUS_UNKONW'
        ]);
        $router = new RouterMiddleware($collector, 'route');
        $this->setProperty($router, 'dispatcher', $dispatcher);
        $router->process($request, $requestHandler);
    }

    public function testGet()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $middleware = $this->createMock(MiddlewareInterface::class);
        $result = $router->get('/foo', 'controller', [$middleware], 'test');
        $this->assertInstanceOf(Route::class, $result);
    }

    public function testPost()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $middleware = $this->createMock(MiddlewareInterface::class);
        $result = $router->post('/foo', 'controller', [$middleware], 'test');
        $this->assertInstanceOf(Route::class, $result);
    }

    public function testPut()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $middleware = $this->createMock(MiddlewareInterface::class);
        $result = $router->put('/foo', 'controller', [$middleware], 'test');
        $this->assertInstanceOf(Route::class, $result);
    }

    public function testDelete()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $middleware = $this->createMock(MiddlewareInterface::class);
        $result = $router->delete('/foo', 'controller', [$middleware], 'test');
        $this->assertInstanceOf(Route::class, $result);
    }

    public function testOptions()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');

        $middleware = $this->createMock(MiddlewareInterface::class);
        $result = $router->options('/foo', 'controller', [$middleware], 'test');
        $this->assertInstanceOf(Route::class, $result);
    }

    public function testAttachCollection()
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);

        $collector->expects($this->once())
            ->method('attach')
            ->with('GET', '/user', 'Targe@index', ['middlewares' => [$middleware], 'name' => 'Alex']);

        $route = $this->createMock(Route::class);
        $route->expects($this->once())->method('getHandler')->willReturn('Targe@index');
        $route->expects($this->once())->method('getPattern')->willReturn('/user');
        $route->expects($this->once())->method('getHttpMethods')->willReturn('GET');
        $route->expects($this->once())->method('getName')->willReturn('Alex');
        $route->expects($this->once())->method('getMiddlewares')->willReturn([
            $middleware
        ]);

        $this->setProperty($router, 'routes', [$route]);
        $this->callMethod($router, 'attachCollection');
    }

    public function testGetRouteParser()
    {
        $router = new RouterMiddleware();
        $result = $router->getRouteParser();
        $this->assertInstanceOf(RouteParserInterface::class, $result);
    }
}
