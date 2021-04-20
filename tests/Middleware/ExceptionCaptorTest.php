<?php

use ConstanzeStandard\Fluff\Middleware\ExceptionCaptor;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class ExceptionCaptorTest extends AbstractTest
{
    /**
     * @throws ReflectionException
     */
    public function testWithExceptionHandler()
    {
        $middleware = new ExceptionCaptor();
        $response = new Response(200, [], ' ');
        $callback = function() use ($response) {
            return $response;
        };

        $middleware->withExceptionHandler(Exception::class, $callback);
        $result = $this->getProperty($middleware, 'exceptionHandlers');
        $this->assertEquals($result, [Exception::class => $callback]);
    }

    /**
     * @throws Throwable
     */
    public function testProcessPass()
    {
        $middleware = new ExceptionCaptor();
        /** @var MockObject|ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var MockObject|RequestHandlerInterface $mockHandler */
        $mockHandler = $this->createMock(RequestHandlerInterface::class);
        $response = new Response(200, [], ' ');
        $mockHandler->expects($this->once())->method('handle')->willReturn($response);

        $result = $middleware->process($mockRequest, $mockHandler);
        $this->assertEquals($result, $response);
    }

    /**
     * @throws Throwable
     */
    public function testProcessError()
    {
        $this->expectException(Throwable::class);
        $middleware = new ExceptionCaptor();
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var MockObject|RequestHandlerInterface $mockHandler */
        $mockHandler = $this->createMock(RequestHandlerInterface::class);
        $mockHandler->expects($this->once())->method('handle')->willThrowException(new Exception());
        $middleware->process($mockRequest, $mockHandler);
    }

    /**
     * @throws Throwable
     */
    public function testProcessErrorResponse()
    {
        $middleware = new ExceptionCaptor();
        /** @var MockObject|ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var MockObject|RequestHandlerInterface $mockHandler */
        $mockHandler = $this->createMock(RequestHandlerInterface::class);
        $response = new Response(200, [], ' ');
        $mockHandler->expects($this->once())->method('handle')->willThrowException(new Exception());
        $callback = function() use ($response) {
            return $response;
        };
        $middleware->withExceptionHandler(Exception::class, $callback);
        $result = $middleware->process($mockRequest, $mockHandler);
        $this->assertEquals($result, $response);
    }

    /**
     * @throws Throwable
     */
    public function testProcessChildErrorResponse()
    {
        $middleware = new ExceptionCaptor();
        /** @var MockObject|ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var MockObject|RequestHandlerInterface $mockHandler */
        $mockHandler = $this->createMock(RequestHandlerInterface::class);
        $response = new Response(200, [], ' ');
        $mockHandler->expects($this->once())->method('handle')->willThrowException(new RuntimeException());
        $callback = function() use ($response) {
            return $response;
        };
        $middleware->withExceptionHandler(Exception::class, $callback);
        $result = $middleware->process($mockRequest, $mockHandler);
        $this->assertEquals($result, $response);
    }
}
