<?php

use Hoo\WordPressPluginFramework\Router;

use function DI\autowire;

return [
	Router\RouterInterface::class => autowire(Router\Router::class),
	Router\Routes\RoutesBuilderInterface::class => autowire(Router\Routes\RoutesBuilder::class),
];
