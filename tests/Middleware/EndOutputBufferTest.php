<?php

use ConstanzeStandard\Fluff\Component\Route;
use ConstanzeStandard\Fluff\Component\RouteGroup;
use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../AbstractTest.php';

class EndOutputBufferTest extends AbstractTest
{
    public function testCloseOutputBuffersWithFlushable()
    {
        $endOutputBuffer = new EndOutputBuffer();
        $this->callMethod($endOutputBuffer, 'closeOutputBuffers', [0]);
        $level = ob_get_level();
        $this->assertEquals(0, $level);
        ob_start();
    }

    public function testCloseOutputBuffersWithCleanable()
    {
        $endOutputBuffer = new EndOutputBuffer();
        $this->callMethod($endOutputBuffer, 'closeOutputBuffers', [0, false]);
        $level = ob_get_level();
        $this->assertEquals(0, $level);
        ob_start();
    }

    public function testCloseOutputBuffersWithFastcgiFinishRequest()
    {
        $endOutputBuffer = new EndOutputBuffer();
        function fastcgi_finish_request() {
            ob_end_clean();
        }
        $this->callMethod($endOutputBuffer, 'closeOutputBuffers', [0, true]);
        $level = ob_get_level();
        $this->assertEquals(0, $level);
        ob_start();
    }

    /**
     * @runInSeparateProcess
     */
    public function testProcess()
    {
        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())->method('isSeekable')->willReturn(true);
        $body->expects($this->once())->method('rewind');
        $body->expects($this->exactly(0))->method('getSize')->willReturn(2);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getProtocolVersion')->willReturn('1.1');
        $response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $response->expects($this->once())->method('getReasonPhrase')->willReturn('OK');
        $response->expects($this->once())->method('getHeaders')->willReturn([
            'Content-Type' => ['text/html']
        ]);
        $response->expects($this->once())->method('getBody')->willReturn($body);
        $response->expects($this->once())->method('getHeaderLine')->with('Content-Length')->willReturn(2);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $endOutputBuffer = new EndOutputBuffer();
        $endOutputBuffer->process($request, $requestHandler);
        ob_start();
    }

    /**
     * @runInSeparateProcess
     */
    public function testProcessWithoutLength()
    {
        $body = Stream::create();

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getProtocolVersion')->willReturn('1.1');
        $response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $response->expects($this->once())->method('getReasonPhrase')->willReturn('OK');
        $response->expects($this->once())->method('getHeaders')->willReturn([
            'Content-Type' => ['text/html']
        ]);
        $response->expects($this->once())->method('getBody')->willReturn($body);
        $response->expects($this->once())->method('getHeaderLine')->with('Content-Length')->willReturn(null);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getMethod')->willReturn('GET');

        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $endOutputBuffer = new EndOutputBuffer();
        $endOutputBuffer->process($request, $requestHandler);
        ob_start();
    }
}
