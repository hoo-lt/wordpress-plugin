<?php

namespace Hoo\WordPressPlugin\Http\Url;

use Hoo\WordPressPlugin\Application\Application;
use Hoo\WordPressPluginFramework\{
    Http\Url\UrlInterface,
    Http\Url\UrlFactoryInterface,
};

function url(string $url): UrlInterface
{
    return Application::instance()->container()->get(UrlFactoryInterface::class)->create($url);
}