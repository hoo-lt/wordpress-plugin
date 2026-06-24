<?php

use wpdb;

use function DI\factory;

return [
	wpdb::class => factory(fn() => $GLOBALS['wpdb']),
];
