<?php

namespace Hoo\WordPressPlugin\Cache;

use Hoo\WordPressPlugin\Application\Application;
use Hoo\WordPressPluginFramework\Cache\CacheInterface;

function cache(): CacheInterface
{
    return Application::instance()->container()->get(CacheInterface::class);
}