<?php

use Hoo\WordPressPluginFramework\Json;

use function DI\autowire;

return [
	Json\JsonInterface::class => autowire(Json\Json::class),
];
