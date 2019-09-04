<?php

use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use ConstanzeStandard\Fluff\Middleware\ExceptionCaptor;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app->get('/user', function(ServerRequestInterface $request) {
    return new Response(200, [], $request->getAttribute('say'));
});

$exceptionCaptor = $app->withMiddleware(new ExceptionCaptor());
$exceptionCaptor->withExceptionHandler(NotFoundException::class, function() {
    return new Response(200, [], 'notfound');
});

$app->withMiddleware(new EndOutputBuffer());

$serverRequest = new ServerRequest('GET', '/user');
$serverRequest = $serverRequest->withAttribute('say', 'hello');
$response = $app->start($serverRequest);
