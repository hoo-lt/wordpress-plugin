<?php

namespace Hoo\WordPressPlugin;

use Closure;

use Hoo\WordPressPlugin\Application\Application;
use Psr\Container\ContainerInterface;

function dir(): string
{
    return Application::instance()->dir();
}


function file(): string
{
    return Application::instance()->file();
}

function container(): ContainerInterface
{
    return Application::instance()->container();
}

function controller(string $class, string $method): Closure
{
    return fn(...$args) => container()->get($class)->$method(...$args);
}

function action(string $action): Closure
{
    return fn(...$args) => container()->get($action)(...$args);
}