<?php

use Hoo\WordPressPluginFramework\Exceptions;

use function DI\autowire;

return [
	Exceptions\Handler\HandlerInterface::class => autowire(Exceptions\Handler\Handler::class),
];
