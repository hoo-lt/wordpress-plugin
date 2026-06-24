<?php

use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();

foreach (glob(__DIR__ . '/definitions/*.php') as $definitions) {
    $containerBuilder->addDefinitions($definitions);
}

return $containerBuilder->build();
