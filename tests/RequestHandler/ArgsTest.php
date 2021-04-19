<?php

use ConstanzeStandard\Fluff\RequestHandler\Args;
use ConstanzeStandard\Fluff\RequestHandler\Vargs;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../AbstractTest.php';

class ArgsTest extends AbstractTest
{
    public function testArgsHandle()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();
        $func = function() use ($response) {
            return $response;
        };
        $handler = new Args($func);
        $result = $handler->handle($request);
        $this->assertEquals($result, $response);
    }

    public function testVargsHandle()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $response = new Response();
        $func = function(ServerRequestInterface $request, $a, $b) use ($response) {
            $this->assertEquals(1, $a);
            $this->assertEquals(2, $b);
            return $response;
        };
        $handler = new Vargs($func, [1, 2]);
        $result = $handler->handle($request);
        $this->assertEquals($result, $response);
    }
}
