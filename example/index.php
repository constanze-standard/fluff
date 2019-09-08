<?php

use Beige\Psr11\Container;
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use ConstanzeStandard\Fluff\InjectableRouter;
use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use ConstanzeStandard\Fluff\Middleware\ExceptionCaptor;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Fluff\RequestHandler\InjectableRequestHandler;
use ConstanzeStandard\Fluff\RequestHandler\RouterRequestHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/../vendor/autoload.php';


$container = new Container();
$injectableRequestHandler = new InjectableRequestHandler($container);
$injectableRequestHandler->onRequestPrepared(function(ServerRequestInterface $request, $c) {
    $c->set(ServerRequestInterface::class, $request);
});
$app = new Application($injectableRequestHandler);


$app->get('/user/{id}', function(ServerRequestInterface $request, $id) {
    echo $id;
    return new Response();
});

$request = new ServerRequest('GET', '/user/12');
$app->handle($request);
