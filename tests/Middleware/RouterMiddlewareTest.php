<?php

use ConstanzeStandard\Fluff\Component\HttpRouter;
use ConstanzeStandard\Fluff\Exception\MethodNotAllowedException;
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
        $result = $router->addMiddleware($middleware);
        $this->assertEquals($result, $middleware);
    }

    public function testWithRoute()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collection', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'GET', '/prefix/foo', 'controller', ['middlewares' => [1], 'name' => 'test']
        );
        $router->withRoute('GET', '/foo', 'controller', [1], 'test');
    }

    public function testWithGroup()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'privMiddlewares', [1]);

        $collector->expects($this->once())->method('attach')->with(
            'GET', '/prefix/foo/bar', 'controller', ['middlewares' => [1,2,3]]
        );
        $router->withGroup('/foo', [2], function($router) {
            $router->withRoute('GET', '/bar', 'controller', [3]);
        });
        $privPrefix = $this->getProperty($router, 'privPrefix');
        $privMiddlewares = $this->getProperty($router, 'privMiddlewares');
        $this->assertEquals($privPrefix, '/prefix');
        $this->assertEquals($privMiddlewares, [1]);
    }

    public function testDispatch()
    {
        /** @var RequestHandlerInterface $requestHandler */
        /** @var ServerRequestInterface $request */
        /** @var CollectionInterface $collector */

        $response = $this->createMock(ResponseInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->once())->method('handle')->willReturn($response);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        $request->expects($this->once())->method('getUri')->willReturn('/foo');
        $request->expects($this->once())->method('withAttribute')
            ->with('route', ['routeHandler', [1], ['id' => 1]])
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
        $this->setProperty($router, 'collection', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'GET', '/prefix/foo', 'controller', ['middlewares' => [1], 'name' => 'test']
        );
        $router->get('/foo', 'controller', [1], 'test');
    }

    public function testPost()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collection', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'POST', '/prefix/foo', 'controller', ['middlewares' => [1], 'name' => 'test']
        );
        $router->post('/foo', 'controller', [1], 'test');
    }

    public function testPut()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collection', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'PUT', '/prefix/foo', 'controller', ['middlewares' => [1], 'name' => 'test']
        );
        $router->put('/foo', 'controller', [1], 'test');
    }

    public function testDelete()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $router = new RouterMiddleware($collector);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collection', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'DELETE', '/prefix/foo', 'controller', ['middlewares' => [1], 'name' => 'test']
        );
        $router->delete('/foo', 'controller', [1], 'test');
    }
}
