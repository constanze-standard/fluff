<?php

use ConstanzeStandard\Fluff\Component\HttpRouter;
use ConstanzeStandard\Route\Collector;
use ConstanzeStandard\Route\Dispatcher;
use GuzzleHttp\Psr7\ServerRequest;

require __DIR__ . '/../vendor/autoload.php';

class A
{
    public function aa()
    {
        echo 123;
    }
}

function test(callable $handler) {
    print_r($handler);
}

test([new A, 'aa']);