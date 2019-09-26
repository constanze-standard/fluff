# Fluff Micro Framework

[![GitHub license](https://img.shields.io/badge/license-Apache%202-blue)](https://github.com/constanze-standard/request-handler/blob/master/LICENSE)

你在为 web 开发寻找一块自由、舒适的乐土？请使用 Fluff，让创作回归自然。

## Fluff 是什么？
- Fluff 是一个简单高效的 PHP [微框架](https://en.wikipedia.org/wiki/Microframework)，它为软件架构提供多种形式的解决方案，但它不会替你做出多余的决策，你将因此获得对于项目的全面掌控。
- Fluff 是一个能够随需求的增加而不断成长的渐进式框架。从一段处理逻辑，到一个庞大的架构，它可以以任何形式出现在你的程序之中。

## 安装
```bash
composer require constanze-standard/fluff "^1.0"
```

## 示例
需要安装组件 [`nyholm/psr7`](https://github.com/Nyholm/psr7)
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
