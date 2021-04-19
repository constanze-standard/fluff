<?php

use ConstanzeStandard\Fluff\RequestHandler\Delay;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class StringTest2
{
    public function index(): Response
    {
        return new Response();
    }

    public function __invoke(): Response
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
        $definition = function() use ($rh) {
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
        $definition = function() use ($rh) {
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
            $this->assertEquals('index', $method);
            return [$instance, $method];
        };
        $rh = $this->createMock(RequestHandlerInterface::class);
        $rh->expects($this->once())->method('handle')->with($request)->willReturn($response);
        $definition = function() use ($rh) {
            return $rh;
        };
        $handler = 'StringTest2@index';
        $delay = new Delay($strategy, $definition, $handler);
        $result = $delay->handle($request);
        $this->assertEquals($result, $response);
    }

    public function testHandleWithInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $strategy = function($className, $method) {
            $instance = new $className;
            $this->assertInstanceOf(StringTest2::class, $instance);
            $this->assertEquals('index', $method);
            return [$instance, $method];
        };
        $rh = $this->createMock(RequestHandlerInterface::class);
        $definition = function() use ($rh) {
            return $rh;
        };
        $handler = [1,2];
        $delay = new Delay($strategy, $definition, $handler);
        $delay->handle($request);
    }
}
