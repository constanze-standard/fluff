<?php

use ConstanzeStandard\Fluff\RequestHandler\Handler;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../AbstractTest.php';

class HandlerTest extends AbstractTest
{
    public function testHandle()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();
        $func = function(ServerRequestInterface $request) use ($response) {
            return $response;
        };
        $handler = new Handler($func);
        $result = $handler->handle($request);
        $this->assertEquals($result, $response);
    }
}
