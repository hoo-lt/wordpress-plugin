<?php

namespace Hoo\WordPressPlugin;

use Closure;
use Hoo\WordPressPluginFramework\{
    Hooker\Hooks\HooksBuilderInterface,
    Hooker\HookerInterface,
    Router\Routes\RoutesBuilderInterface,
    Router\RouterInterface,
};
use Psr\Container\ContainerInterface;

function container(?ContainerInterface $container = null): ?ContainerInterface
{
    static $static;
    return $static ??= $container;
}

function file(?string $file = null): ?string
{
    static $static;
    return $static ??= $file;
}

function hook(): HooksBuilderInterface
{
    $hooksBuilder = container()->get(HooksBuilderInterface::class);
    return $hooksBuilder;
}

function route(): RoutesBuilderInterface
{
    $routesBuilder = container()->get(RoutesBuilderInterface::class);
    return $routesBuilder;
}

function controller(string $controller): object
{
    $controller = container()->get($controller);
    return $controller;
}

function action(string $action): Closure
{
    $action = container()->get($action);
    return fn(...$args) => $action(...$args);
}

function boot(string $file): void
{
    file($file);

    $container = require dirname($file) . '/container/container.php';
    container($container);

    $hooksBuilder = require dirname($file) . '/hooks.php';

    container()->get(HookerInterface::class)
        ->withHooks(
            ...$hooksBuilder->build(),
        )
    ();

    $routesBuilder = require dirname($file) . '/routes.php';

    container()->get(RouterInterface::class)
        ->withRoutes(
            ...$routesBuilder->build(),
        )
    ();
}