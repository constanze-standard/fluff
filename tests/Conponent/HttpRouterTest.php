<?php

use ConstanzeStandard\Fluff\Component\HttpRouter;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;
use ConstanzeStandard\Route\Interfaces\DispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../AbstractTest.php';

class HttpRouterTest extends AbstractTest
{
    public function testWithRoute()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $router = new HttpRouter($collector, $dispatcher);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collector', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'GET', '/prefix/foo', 'controller', []
        );
        $router->withRoute('GET', '/foo', 'controller');
    }

    public function testWithGroup()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $router = new HttpRouter($collector, $dispatcher);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'privData', ['a' => 1]);

        $collector->expects($this->once())->method('attach')->with(
            'GET', '/prefix/foo/bar', 'controller', ['a' => [1,2,3]]
        );
        $router->withGroup('/foo', ['a' => 2], function($router) {
            $router->withRoute('GET', '/bar', 'controller', ['a' => 3]);
        });
        $privPrefix = $this->getProperty($router, 'privPrefix');
        $privData = $this->getProperty($router, 'privData');
        $this->assertEquals($privPrefix, '/prefix');
        $this->assertEquals($privData, ['a' => 1]);
    }

    public function testDispatch()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');
        $request->expects($this->once())->method('getUri')->willReturn('/foo');
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->willReturn([]);
        $router = new HttpRouter($collector, $dispatcher);
        $result = $router->dispatch($request);
        $this->assertEquals($result, []);
    }

    public function testGet()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $router = new HttpRouter($collector, $dispatcher);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collector', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'GET', '/prefix/foo', 'controller', []
        );
        $router->get('/foo', 'controller');
    }

    public function testPost()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $router = new HttpRouter($collector, $dispatcher);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collector', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'POST', '/prefix/foo', 'controller', []
        );
        $router->post('/foo', 'controller');
    }

    public function testPut()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $router = new HttpRouter($collector, $dispatcher);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collector', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'PUT', '/prefix/foo', 'controller', []
        );
        $router->put('/foo', 'controller');
    }

    public function testDelete()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $router = new HttpRouter($collector, $dispatcher);
        $this->setProperty($router, 'privPrefix', '/prefix');
        $this->setProperty($router, 'collector', $collector);

        $collector->expects($this->once())->method('attach')->with(
            'DELETE', '/prefix/foo', 'controller', []
        );
        $router->delete('/foo', 'controller');
    }
}
