# Fluff

[![GitHub license](https://img.shields.io/github/license/alienwow/SnowLeopard.svg)](https://github.com/alienwow/SnowLeopard/blob/master/LICENSE)

## 关于 Fluff
Fluff 是一个为 web 应用打造的微框架。它整合了当今主流的实践标准，帮助你实现高可用性的应用程序。

我们相信“让专业的人做专业的事”会带来最好的结果，在软件架构中也是如此。Fluff 专注于”融合各方的力量“来解决问题，使用 Fluff 的开发者也是如此，我们给与使用者最大限度的选择权，挑选最专业的或最适合的应用组件 将使你的程序更好更快的运转。

## 安装
```bash
composer install constanze-standard/fluff
```

## 开始使用
```php
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use ConstanzeStandard\Fluff\Application;

$app = new Application();

$app->get('/hello/{name}', function($name) {
    $response = new Response();
    $response->getBody()->write('hello ' . $name);
    return $response;
});

$app->start(new ServerRequest('GET', '/hello/world'));
```

## 学习 Fluff
请前往 [fluff wiki](https://github.com/constanze-standard/fluff/wiki) 查看帮助文档。
