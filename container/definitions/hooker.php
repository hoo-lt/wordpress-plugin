<?php

use Hoo\WordPressPluginFramework\Hooker;

use function DI\autowire;

return [
	Hooker\HookerInterface::class => autowire(Hooker\Hooker::class),
	Hooker\Hooks\HooksBuilderInterface::class => autowire(Hooker\Hooks\HooksBuilder::class),
];
