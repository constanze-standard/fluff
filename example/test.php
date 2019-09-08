<?php

use ConstanzeStandard\Fluff\Component\HttpRouter;
use ConstanzeStandard\Route\Collector;
use ConstanzeStandard\Route\Dispatcher;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;

require __DIR__ . '/../vendor/autoload.php';

$routerMiddlware = $app->withMiddleware(new RouterMiddleware($app));

$router = $routerMiddlware->getRouter();

$router->get('/user', function() {
    return new Response();
});

$request = new ServerRequest('GET', '/user');
$routerMiddlware->start($request);