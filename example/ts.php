<?php

use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use ConstanzeStandard\Fluff\RequestHandler\SingleHandler;
// use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Http\Message\RequestFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/../vendor/autoload.php';

class VerificationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        if (array_key_exists('words', $queryParams)) {
            return $handler->handle($request);
        }
        return new Response(400, ['Content-Type' => 'text/plain']);
    }
}

$requestHandler = new SingleHandler(function(ServerRequestInterface $request) {
    $queryParams = $request->getQueryParams();
    $words = $queryParams['words'];

    $words = str_replace(
        ['吗', '?', '？'],
        ['', '!', '！'],
        $words
    );
    return new Response(200, ['Content-Type' => 'text/plain'], $words);
});
$app = new Application($requestHandler);
$app->addMiddleware(new VerificationMiddleware());
$app->addMiddleware(new EndOutputBuffer());

$psr17Factory = new Psr17Factory();
$creator = new ServerRequestCreator(
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
    $psr17Factory
);
$rquest = $creator->fromGlobals();

// $request = $request->withQueryParams(['words' => '在？']);
$app->handle($rquest);