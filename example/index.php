<?php

use Beige\Psr11\Container;
use ConstanzeStandard\Fluff\Application;

require __DIR__ . '/../vendor/autoload.php';

class Test
{
    public function hello()
    {
        echo 'hello world';
    }
}

$container = new Container([
    Test::class => new Test()
]);

$app = new Application($container);

$app(function($a, Test $test) {
    $test->hello();
}, ['a' => 123, 'b' => 21]);
