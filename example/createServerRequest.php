<?php

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;

$server = $_SERVER;
$headers = [];

if (false === isset($server['REQUEST_METHOD'])) {
    $server['REQUEST_METHOD'] = 'GET';
}
$httpMethod = $server['REQUEST_METHOD'] ?? 'GET';
$protocol = str_replace('HTTP/', '', $server['SERVER_PROTOCOL'] ?? '1.1');

foreach ($server as $key => $value) {
    if ($value && 0 === \strpos($key, 'HTTP_')) {
        $name = \strtr(\strtolower(\substr($key, 5)), '_', '-');
        $headers[$name] = $value;
    }
}

$uri = new Uri();
$uri = $uri->withScheme($server['REQUEST_SCHEME'] ?? ($server['HTTPS'] ? 'https' : 'http'));
$uri = $uri->withPort($server['SERVER_PORT'] ?? null);
$uri = $uri->withHost($server['SERVER_NAME']);
$uri = $uri->withPath(\current(\explode('?', $server['REQUEST_URI'])));
$uri = $uri->withQuery($server['QUERY_STRING'] ?? '');

$serverRequest = new ServerRequest($method, $uri, $server);
foreach ($headers as $name => $value) {
    $serverRequest = $serverRequest->withAddedHeader($name, $value);
}

$serverRequest = $serverRequest
    ->withProtocolVersion($protocol)
    ->withCookieParams($_COOKIE)
    ->withQueryParams($_GET)
    ->withParsedBody($_POST);
