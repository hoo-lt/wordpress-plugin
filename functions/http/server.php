<?php

namespace Hoo\WordPressPlugin\Http\Server;

use Hoo\WordPressPlugin\Application\Application;
use Hoo\WordPressPluginFramework\{
    Http\Server\Request\RequestInterface,
    Http\Server\Request\RequestFactoryInterface,
    Http\Server\Response\ResponseInterface,
    Http\Server\Response\ResponseFactoryInterface,
};

function request(string $method, string $url, ?array $headers = null, array|string|null $body = null): RequestInterface
{
    return Application::instance()->container()->get(RequestFactoryInterface::class)->create($method, $url, $headers, $body);
}

function response(int $statusCode, ?array $headers = null, array|string|null $body = null): ResponseInterface
{
    return Application::instance()->container()->get(ResponseFactoryInterface::class)->create($statusCode, $headers, $body);
}