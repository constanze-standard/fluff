<?php

use ConstanzeStandard\Fluff\Component\DispatchInformation;
use Psr\Http\Server\MiddlewareInterface;

require_once __DIR__ . '/../AbstractTest.php';

class DispatchInformationTest extends AbstractTest
{
    public function testGetHandler()
    {
        $dispatchInformation = new DispatchInformation('handler');
        $this->assertEquals($dispatchInformation->getHandler(), 'handler');
    }

    public function testGetMiddlewares()
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $dispatchInformation = new DispatchInformation(
            '', [$middleware]
        );
        $this->assertEquals($dispatchInformation->getMiddlewares(), [$middleware]);
    }

    public function testGetArguments()
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $dispatchInformation = new DispatchInformation(
            '', [], [1, 2]
        );
        $this->assertEquals($dispatchInformation->getArguments(), [1, 2]);
    }
}
