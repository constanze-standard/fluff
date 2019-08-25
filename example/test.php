<?php

use ConstanzeStandard\Fluff\Conponent\HttpRouter;
use ConstanzeStandard\Route\Collector;
use ConstanzeStandard\Route\Dispatcher;
use GuzzleHttp\Psr7\ServerRequest;

require __DIR__ . '/../vendor/autoload.php';


$collector = new Collector();
$dispatcher = new Dispatcher($collector);

$router = new HttpRouter($collector, $dispatcher);

$router->withGroup('/user', ['name' => 'awsl'], function($router) {
    $router->get('/cat', function() {

    }, ['name' => 'user']);
});

$router->get('/user', function() {

}, ['name' => 'user']);

$serverRequest = new ServerRequest('GET', '/user/cat');
$result = $router->dispatch($serverRequest);
print_r($result);
