# Fluff

[![GitHub license](https://img.shields.io/badge/license-Apache%202-blue)](https://github.com/constanze-standard/request-handler/blob/master/LICENSE)

## 关于 Fluff
Fluff 是一个为 web 应用打造的微框架。它整合了当今主流的实践标准，帮助你实现高可用性的应用程序。

Fluff 并不是“开箱即用”的框架，我们希望在合理的架构之下，对应用的各个层面做到最细粒度的控制，不同组件相互配合，可以衍生出多种架构风格。这也意味着相比传统意义上的 MVC 框架会有更多的前期准备，但随着工作的进行，应用程序也将更加符合你的预期。

## 安装
```bash
composer install constanze-standard/fluff "^2.0"
```

## 开始使用
```php
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\Middleware\EndOutputBuffer;
use ConstanzeStandard\Fluff\RequestHandler\Handler;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;

require __DIR__ . '/vendor/autoload.php';

$handler = new Handler(function(ServerRequestInterface $request) {
    return new Response(200, [], 'hello world');
});
$app = new Application($handler);
$app->addMiddleware(new EndOutputBuffer());

$request = new ServerRequest('GET', '/');
$app->handle($request);
```

## 学习 Fluff
请前往 [Fluff 文档页](https://constanze-standard.github.io/fluff-framework-documentation/) 查看帮助文档。
