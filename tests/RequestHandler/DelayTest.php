<?php

use ConstanzeStandard\Container\Container;
use ConstanzeStandard\Fluff\RequestHandler\Args;
use ConstanzeStandard\Fluff\RequestHandler\Delay;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class StringTest2
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

class DelayTest extends AbstractTest
{
    public function testGetDefinition()
    {
        $rh = $this->createMock(RequestHandlerInterface::class);
        $strategy = function($className, $method) {
            return [new $className, $method];
        };
        $definition = function($handler, array $arguments) use ($rh) {
            return $rh;
        };
        $handler = function() {

        };
        $def = Delay::getDefinition($strategy, $definition);
        $result = $def($handler, []);
        $this->assertInstanceOf(Delay::class, $result);
    }

    public function testHandleWithCallable()
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $strategy = function($className, $method) {
            return [new $className, $method];
        };
        $rh = $this->createMock(RequestHandlerInterface::class);
        $rh->expects($this->once())->method('handle')->with($request)->willReturn($response);
        $definition = function($handler, $arguments) use ($rh) {
            return $rh;
        };
        $handler = function() use ($response) {
            return $response;
        };
        $delay = new Delay($strategy, $definition, $handler);
        $result = $delay->handle($request);
        $this->assertEquals($result, $response);
    }

    public function testHandleWithString()
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $strategy = function($className, $method) {
            $instance = new $className;
            $this->assertInstanceOf(StringTest2::class, $instance);
            $this->assertEquals($method, 'index');
            return [$instance, $method];
        };
        $rh = $this->createMock(RequestHandlerInterface::class);
        $rh->expects($this->once())->method('handle')->with($request)->willReturn($response);
        $definition = function($handler, $arguments) use ($rh) {
            return $rh;
        };
        $handler = 'StringTest2@index';
        $delay = new Delay($strategy, $definition, $handler);
        $result = $delay->handle($request);
        $this->assertEquals($result, $response);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHandleWithInvalidArgumentException()
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $strategy = function($className, $method) {
            $instance = new $className;
            $this->assertInstanceOf(StringTest2::class, $instance);
            $this->assertEquals($method, 'index');
            return [$instance, $method];
        };
        $rh = $this->createMock(RequestHandlerInterface::class);
        $definition = function($handler, $arguments) use ($rh) {
            return $rh;
        };
        $handler = [1,2];
        $delay = new Delay($strategy, $definition, $handler);
        $result = $delay->handle($request);
    }
}
