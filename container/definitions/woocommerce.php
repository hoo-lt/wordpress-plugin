<?php

use WC_Logger_Interface;

use function DI\factory;

return [
	WC_Logger_Interface::class => factory(fn() => wc_get_logger()),
];
