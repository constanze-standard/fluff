<?php

use Beige\Psr11\Container;
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Event\ExceptionEvent;
use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use ConstanzeStandard\Fluff\Middleware\ExceptionCaptor;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Fluff\RequestHandler\DiHandler;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use ConstanzeStandard\Fluff\RequestHandler\InjectableRequestHandler;
use ConstanzeStandard\Fluff\RequestHandler\InjectableRouteHandler;
use ConstanzeStandard\Fluff\RequestHandler\LazyHandler;
use ConstanzeStandard\Fluff\RequestHandler\RouteHandler;
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



// $container = new Container();

// $app = new Application(new InjectableRouteHandler($container));

// $routerMiddleware = $app->addMiddleware(new RouterMiddleware());

// $routerMiddleware->addMiddleware(new CatchRequestMiddleware($container));

// $routerMiddleware->withRoute('GET', '/user/{id}', function(ServerRequestInterface $reqeust, $id) {
//     echo $reqeust->getAttribute('say');
//     return new Response();
// }, [
//     new HelloMiddleware()
// ]);

// $request = new ServerRequest('GET', '/user/12');
// $app->handle($request);

// $requestHandler = new SingleHandler(function(ServerRequestInterface $request) {
//     return new Response(200, [], 'hello world');
// });
// $app = new Application($requestHandler);
// $app->addMiddleware(new EndOutputBuffer());

// $request = new ServerRequest('GET', '/user/12');
// $app->handle($request);

class Ctrl
{
    public $a = 12;

    public function __invoke(Ctrl $ctrl)
    {
        echo $ctrl->a;
        return new Response();
    }
}

$container = new Container();
$container->set(Ctrl::class, new Ctrl);
$handler = new RouteHandler(DiHandler::getDefinition($container));
$app = new Application($handler);

/** @var RouterMiddleware $router */
$router = $app->addMiddleware(new RouterMiddleware());
$router->get('/user', Ctrl::class);

$app->addMiddleware(new EndOutputBuffer());

$request = new ServerRequest('GET', '/user');
$app->handle($request);
