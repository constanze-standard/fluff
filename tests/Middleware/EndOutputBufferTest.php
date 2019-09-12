<?php

use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class EndOutputBufferTest extends AbstractTest
{
    public function testProcess()
    {
        $middleware = new EndOutputBuffer(1);
        $response = new Response(200, [], ' ');
        /** @var ServerRequestInterface $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var RequestHandlerInterface $mockHandler */
        $mockHandler = $this->createMock(RequestHandlerInterface::class);
        $mockHandler->expects($this->once())->method('handle')->willReturn($response);
        $result = $middleware->process($mockRequest, $mockHandler);
        $this->assertEquals($result, $response);
        ob_start();
    }

    public function testRespondClean()
    {
        $middleware = new EndOutputBuffer();
        $response = new Response(200, [], ' ');
        $middleware->respond($response, false);
        $status = ob_get_status();
        $content = file_get_contents('php://output');
        $this->assertEmpty($status);
        $this->assertEmpty($content);
        ob_start();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRespondHeader()
    {
        $middleware = new EndOutputBuffer(1, false);
        $response = new Response(200, ['Content-Type' => 'text/plain']);
        $this->callMethod($middleware, 'respondHeader', [$response]);
        $this->assertFalse(headers_sent());
    }

    public function testEndOutputBuffersWithFastcgi()
    {
        if (!\function_exists('fastcgi_finish_request')) {
            function fastcgi_finish_request() {
                return true;
            }
        }
        $middleware = new EndOutputBuffer();
        $result = $this->callMethod($middleware, 'endOutputBuffers', [true]);
        $this->assertTrue($result);
    }
}
