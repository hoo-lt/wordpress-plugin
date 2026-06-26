<?php

namespace Hoo\WordPressPlugin\Cache;

use Hoo\WordPressPlugin\Application\Application;
use Hoo\WordPressPluginFramework\{
    Http\Client\ClientInterface,
    Http\Client\Request\RequestInterface,
    Http\Client\Request\RequestFactoryInterface,
    Http\Client\Response\ResponseInterface,
    Http\Client\Response\ResponseFactoryInterface,
};

function client(): ClientInterface
{
    return Application::instance()->container()->get(ClientInterface::class);
}

function request(string $method, string $url, ?array $headers = null, array|string|null $body = null): RequestInterface
{
    return Application::instance()->container()->get(RequestFactoryInterface::class)->create($method, $url, $headers, $body);
}

function response(int $statusCode, ?array $headers = null, array|string|null $body = null): ResponseInterface
{
    return Application::instance()->container()->get(ResponseFactoryInterface::class)->create($statusCode, $headers, $body);
}