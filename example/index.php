<?php

use Beige\Psr11\Container;
use Beige\Psr11\DefinitionCollection;
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
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

class RegexFilter
{
    const IS_NUMERIC = '^[0-9]+$';

    const IS_WORD = '^\w$';

    public function __invoke(ServerRequestInterface $serverRequest, array $options, $params)
    {
        foreach ($options as $paramName => $regex) {
            if (isset($params[$paramName])) {
                if (! preg_match('/' . $regex . '/', $params[$paramName])) {
                    return false;
                }
            }
        }
        return true;
    }
}

$container = new Container([
    Test::class => new Test()
]);

$app = new Application($container, [
    // 'route_cache' => __DIR__ . '/route_cache.php'
]);

$app->withExceptionHandler(NotFoundException::class, function() {
    return new Response(200, [], 'NotFoundException');
});

$app->withFilter('regex', new RegexFilter());

$app->get('/user/id_{id}', function (ServerRequestInterface $request, $id) use ($app) {
    // $word = $request->getAttribute('say');
    // $routeParser = $app->getRouteParser();
    // $url = $routeParser->getUrlByName('user', ['id' => $id]);
    // $say = $request->getAttribute('say');
    $response = new Response(200, [], $id);
    return $response;
}, [
    'name' => 'user',
    'filters' => [
        'regex' => ['id' => '^[0-9]+$']
    ]
]);

$serverRequest = new ServerRequest('GET', urlencode('/users/id_123'));

$app->start($serverRequest);
