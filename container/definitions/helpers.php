<?php

use Hoo\WordPressPluginFramework\Helpers;

use function DI\autowire;

return [
	Helpers\KeyValue\HelperInterface::class => autowire(Helpers\KeyValue\Helper::class),
];
