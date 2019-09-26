# Fluff Micro Framework

[![GitHub license](https://img.shields.io/badge/license-Apache%202-blue)](https://github.com/constanze-standard/request-handler/blob/master/LICENSE)

## 关于 Fluff
Fluff 是一个为 web 应用打造的微框架。它整合了当今主流的实践标准，帮助你实现高可用性的应用程序。

Fluff 并不是“开箱即用”的框架，我们希望在合理的架构之下，对应用的各个层面做到最细粒度的控制，不同组件相互配合，可以衍生出多种架构风格。这也意味着相比传统意义上的 MVC 框架会有更多的前期准备，但随着工作的进行，应用程序也将更加符合你的预期。

## 安装
```bash
composer require constanze-standard/fluff "^1.0"
```

## 最小应用示例
```php
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use ConstanzeStandard\Fluff\Middleware\RouterMiddleware;
use ConstanzeStandard\Fluff\RequestHandler\Dispatcher;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

require __DIR__ . '/../vendor/autoload.php';

$dispatcher = new Dispatcher(Handler::getDefinition());
$app = new Application($dispatcher);

$router = $app->addMiddleware(new RouterMiddleware());

$router->get('/love/{name}', function(ServerRequestInterface $request, $args) {
    return new Response(200, [], "I ♥ {$args['name']}!");
});

$app->addMiddleware(new EndOutputBuffer());
$app->handle(new ServerRequest('GET', '/love/Fluff'));
```

## 学习 Fluff
请前往 [Fluff 文档页](https://constanze-standard.github.io/fluff-framework-documentation/) 查看帮助文档。
