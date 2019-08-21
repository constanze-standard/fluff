<?php

use Beige\Psr11\Container;
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/../vendor/autoload.php';

class Test
{
    public function hello()
    {
        echo 'hello world';
    }
}

class SayHelloMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('say', 'hello world');
        return $handler->handle($request);
    }

    public static function __set_state($array)
    {
        return new static;
    }
}

$container = new Container([
    Test::class => new Test()
]);

$app = new Application($container);

$app->withExceptionHandler(NotFoundException::class, function() {
    return new Response(200, [], 'NotFoundException');
});

$app->get('/user', function(ServerRequestInterface $request) {
    $word = $request->getAttribute('say');
    $response = new Response(200, [], $word);
    return $response;
}, ['name' => 'user', 'middlewares' => [
    new SayHelloMiddleware()
]]);

$serverRequest = new ServerRequest('GET', '/user');

$app->start($serverRequest);
