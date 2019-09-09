<?php

use ConstanzeStandard\Fluff\Component\RouteParser;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;

require_once __DIR__ . '/../AbstractTest.php';

class RouteParserTest extends AbstractTest
{
    public function testGetRelativeUrlByAttributes()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $collector->expects($this->once())->method('getRoutesByData')
            ->willReturn(['/foo/{id}', null, null, ['id']]);
        $routeParser = new RouteParser($collector);
        $result = $this->callMethod($routeParser, 'getRelativeUrlByAttributes', [[],[
            'id' => 1
        ], ['a' => 1]]);
        $this->assertEquals($result, '/foo/1?a=1');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetRelativeUrlByAttributesRuntimeException()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $collector->expects($this->once())->method('getRoutesByData')
            ->willReturn(null);
        $routeParser = new RouteParser($collector);
        $result = $this->callMethod($routeParser, 'getRelativeUrlByAttributes', [[],[
            'id' => 1
        ], ['a' => 1]]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRelativeUrlByAttributesInvalidArgumentException()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $collector->expects($this->once())->method('getRoutesByData')
            ->willReturn(['/foo/{id}', null, null, ['id']]);
        $routeParser = new RouteParser($collector);
        $result = $this->callMethod($routeParser, 'getRelativeUrlByAttributes', [[],[], ['a' => 1]]);
    }

    public function testGetRelativeUrlByName()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $collector->expects($this->once())->method('getRoutesByData')
            ->willReturn(['/foo/{id}', null, null, ['id']]);
        $routeParser = new RouteParser($collector);
        $result = $routeParser->getRelativeUrlByName('name', ['id' => 1], []);
        $this->assertEquals($result, '/foo/1');
    }

    public function testGetUrlByName()
    {
        /** @var CollectionInterface $collector */
        $collector = $this->createMock(CollectionInterface::class);
        $collector->expects($this->once())->method('getRoutesByData')
            ->willReturn(['/foo/{id}', null, null, ['id']]);
        $routeParser = new RouteParser($collector, '/base');
        $result = $routeParser->getUrlByName('name', ['id' => 1], []);
        $this->assertEquals($result, '/base/foo/1');
    }
}
