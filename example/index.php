<?php

use Beige\Psr11\Container;
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Event\ExceptionEvent;
use ConstanzeStandard\Fluff\Middleware\ExceptionCaptor;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Fluff\RequestHandler\InjectableRequestHandler;
use ConstanzeStandard\Fluff\RequestHandler\InjectableRouteHandler;
use ConstanzeStandard\Fluff\RequestHandler\SingleHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/../vendor/autoload.php';

class CatchRequestMiddleware implements MiddlewareInterface
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->container->set(ServerRequestInterface::class, $request);
        return $handler->handle($request);
    }
}

class HelloMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('say', 'hello');
        return $handler->handle($request);
    }
}



$container = new Container();

$app = new Application(new InjectableRouteHandler($container));

$routerMiddleware = $app->addMiddleware(new RouterMiddleware());

$routerMiddleware->addMiddleware(new CatchRequestMiddleware($container));

$routerMiddleware->withRoute('GET', '/user/{id}', function(ServerRequestInterface $reqeust, $id) {
    echo $reqeust->getAttribute('say');
    return new Response();
}, [
    new HelloMiddleware()
]);

$request = new ServerRequest('GET', '/user/12');
$app->handle($request);
