<?php

namespace Hoo\WordPressPlugin\Application;

use Hoo\WordPressPluginFramework\{
    Hooker\HookerInterface,
    Router\RouterInterface,
};
use Psr\Container\ContainerInterface;

class Application
{
    protected static self $instance;

    protected function __construct(
        protected string $dir,
        protected string $file,
        protected ContainerInterface $container,
    ) {
    }

    public static function boot(string $dir, string $file): void
    {
        $container = require "{$dir}/container/container.php";

        self::$instance = new self($dir, $file, $container);

        self::$instance->hooks();
        self::$instance->routes();
    }

    public static function instance(): self
    {
        return self::$instance;
    }

    public function dir(): string
    {
        return $this->dir;
    }

    public function file(): string
    {
        return $this->file;
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }

    protected function hooks(): void
    {
        $hooksBuilder = require "{$this->dir}/hooks.php";

        $this->container->get(HookerInterface::class)
            ->withHooks(
                ...$hooksBuilder->build(),
            )
        ();
    }

    protected function routes(): void
    {
        $routesBuilder = require "{$this->dir}/routes.php";

        $this->container->get(RouterInterface::class)
            ->withRoutes(
                ...$routesBuilder->build(),
            )
        ();
    }
}
