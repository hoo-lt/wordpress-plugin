<?php

use Hoo\WordPressPluginFramework\Cache;

use function DI\autowire;

return [
	Cache\CacheInterface::class => autowire(Cache\Cache::class)
		->constructorParameter('ttl', HOUR_IN_SECONDS),
];
