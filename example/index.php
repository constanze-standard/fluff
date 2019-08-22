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

$app = new Application($container, [
    'route_cache' => __DIR__ . '/route_cache'
]);

$app->withExceptionHandler(NotFoundException::class, function() {
    return new Response(200, [], 'NotFoundException');
});

function te(ServerRequestInterface $request) {
    $word = $request->getAttribute('say');
    $response = new Response(200, [], $word);
    return $response;
}

// $app->catchRoutes(__DIR__ . '/cache_route');

$app->get('/user/{id}', function (ServerRequestInterface $request, $id) use ($app) {
    $word = $request->getAttribute('say');
    $routeService = $app->getRouteService();
    $url = $routeService->getUrlByName('user', ['id' => 234]);
    $response = new Response(200, [], $url);
    return $response;
}, ['name' => 'user']);

$serverRequest = new ServerRequest('GET', '/user/123');

$app->start($serverRequest);