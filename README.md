# Fluff Micro Framework

[![GitHub license](https://img.shields.io/badge/license-Apache%202-blue)](https://github.com/constanze-standard/fluff/blob/master/LICENSE)

web 开发又一次变得有趣起来了！

## Fluff 是什么？
- Fluff 是一个多核心的 PHP [微框架](https://en.wikipedia.org/wiki/Microframework)，它为应用程序的构建提供多种形式的解决方案。
- Fluff 是一个能够随需求的增加而不断成长的渐进式框架。从一段处理逻辑到一个庞大的架构，它可以以任何形式出现在你的程序之中。

## 安装
```bash
composer require constanze-standard/fluff:^1.0
```

## 示例
需要安装组件 [`nyholm/psr7`](https://github.com/Nyholm/psr7)
```php
use ConstanzeStandard\Fluff\Application;
use ConstanzeStandard\Fluff\RequestHandler\Args;
use ConstanzeStandard\Fluff\RequestHandler\Delay;
use ConstanzeStandard\Fluff\RequestHandler\Dispatcher;

require __DIR__ . '/../vendor/autoload.php';

// 调用策略 ↓
$definition = Args::getDefinition();
// 延迟策略 ↓
$definition = Delay::getDefinition(function($className, $method) {
    return [new $className, $method];
}, $definition);
// 路由派发策略
$core = new Dispatcher($definition);
// 创建应用
$app = new Application($core);
```

## 学习 Fluff
如上例所示，Fluff 的核心是可替换的，选用不同的核心将会启用不同的特性。了解更多使用方式，请访问我们的 [Fluff 官方网站](https://www.fluff-framework.cn/)。
