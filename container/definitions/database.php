<?php

use Hoo\WordPressPluginFramework\Database;

use function DI\autowire;

return [
	Database\DatabaseInterface::class => autowire(Database\Database::class),
	Database\Select\SelectInterface::class => autowire(Database\Select\Select::class),
	Database\Migrator\MigratorInterface::class => autowire(Database\Migrator\Migrator::class)
		->constructorParameter('path', dirname(__DIR__, 2) . '/migrations'),
];
