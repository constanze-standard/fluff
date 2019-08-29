<?php

use Beige\Invoker\Interfaces\InvokerInterface;
use ConstanzeStandard\Fluff\InvokerProxy;

require_once __DIR__ . '/AbstractTest.php';

class InvokerProxyTest extends AbstractTest
{
    public function testCall()
    {
        /** @var InvokerInterface $invoker */
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())->method('call')->willReturn(true);
        $invokerProxy = new InvokerProxy($invoker);
        $result = $invokerProxy->call(function() {}, []);
        $this->assertTrue($result);
    }

    public function testNew()
    {
        $obj = new stdClass();
        /** @var InvokerInterface $invoker */
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())->method('new')->willReturn($obj);
        $invokerProxy = new InvokerProxy($invoker);
        $result = $invokerProxy->new('className', []);
        $this->assertEquals($result, $obj);
    }

    public function testCallMethod()
    {
        $obj = new stdClass();
        /** @var InvokerInterface $invoker */
        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())->method('callMethod')->willReturn(true);
        $invokerProxy = new InvokerProxy($invoker);
        $result = $invokerProxy->callMethod($obj, 'method', []);
        $this->assertTrue($result);
    }
}
