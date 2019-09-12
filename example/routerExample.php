<?php

use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Route\Collector;
use ConstanzeStandard\Route\Interfaces\CollectionInterface;

require __DIR__ . '/../vendor/autoload.php';

$collection = new Collector();
$routerMiddleware = new RouterMiddleware($collection);


$request = new ServerRequest('GET', '/user/World');
