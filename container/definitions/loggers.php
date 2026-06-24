<?php

use Hoo\WordPressPluginFramework\Loggers;

use function DI\autowire;

return [
	Loggers\LoggerInterface::class => autowire(Loggers\WooCommerce\Logger::class)
		->constructorParameter('source', 'wordpress-plugin'),
];
