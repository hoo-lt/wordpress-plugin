<?php

use Hoo\WordPressPluginFramework\Repositories;

use function DI\autowire;

return [
	Repositories\Database\Migrator\RepositoryInterface::class => autowire(Repositories\Database\Migrator\Repository::class)
		->constructorParameter('key', 'wordpress_plugin_migrations'),
];
