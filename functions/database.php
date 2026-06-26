<?php

namespace Hoo\WordPressPlugin\Database;

use Hoo\WordPressPlugin\Application\Application;
use Hoo\WordPressPluginFramework\Database\DatabaseInterface;

function database(): DatabaseInterface
{
    return Application::instance()->container()->get(DatabaseInterface::class);
}