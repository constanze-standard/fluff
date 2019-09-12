<?php

use Beige\Psr11\Container;
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Exception\NotFoundException;
use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use ConstanzeStandard\Fluff\Middleware\ExceptionCaptor;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Fluff\RequestHandler\DiHandler;
use ConstanzeStandard\Fluff\RequestHandler\Dispatcher;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use ConstanzeStandard\Fluff\RequestHandler\DelayHandler;
use Nyholm\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Nyholm\Psr7\ServerRequest as NyholmServerRequest;
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
        $this->container->set(get_class($request), $request);
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

class M implements MiddlewareInterface
{
    public function __construct($num)
    {
        $this->num = $num;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        echo $this->num. ' ';
        return $handler->handle($request);
    }
}

class Service
{
    public function sayHello($name)
    {
        return 'Hello ' . $name;
    }
}

class Target
{
    public function index(Service $service, $name)
    {
        return new Response(200, [], $service->sayHello($name));
    }
}

$container = new Container();
$container->set(Service::class, new Service());
$dispatcher = new Dispatcher(DiHandler::getDefinition($container));
$app = new Application($dispatcher);

/** @var RouterMiddleware $router */
$router = $app->addMiddleware(new RouterMiddleware());

/** @var ExceptionCaptor $exceptionCaptor */
$exceptionCaptor = $app->addMiddleware(new ExceptionCaptor());

$notFoundHandler = function(ServerRequestInterface $request, \Throwable $e) {
    return new Response(404, [], $e->getMessage());
};
$exceptionCaptor->withExceptionHandler(NotFoundException::class, $notFoundHandler);

$app->addMiddleware(new EndOutputBuffer());

$router->addMiddleware(new M(0));
$router->withGroup('', [new M(1)], function($router) {
    $router->get('/user/{name}', 'Target@index')->addMiddleware(new M(2))->addMiddleware(new M(3));
});
$router->addMiddleware(new M(4));

$request = new ServerRequest('GET', '/user/World');
$app->handle($request);
