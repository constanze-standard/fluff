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
}

$container = new Container([
    Test::class => new Test()
]);

$app = new Application($container, [
    // 'route_cache' => __DIR__ . '/route_cache.php',
    'exception_handlers' => [
        NotFoundException::class => function() {
            return new Response(200, [], 'NotFoundException');
        }
    ]
]);

$app->get('/user/{id}', function (ServerRequestInterface $request, $id) use ($app) {
    // $word = $request->getAttribute('say');
    // $routeParser = $app->getRouteParser();
    // $url = $routeParser->getUrlByName('user', ['id' => $id]);
    // $say = $request->getAttribute('say');
    $response = new Response(200, [], $id);
    return $response;
}, [
    'name' => 'user',
    // 'middlewares' => [
    //     SayHelloMiddleware::class
    // ]
]);

$serverRequest = new ServerRequest('GET', urlencode('/user/123'));

$app->start($serverRequest);
