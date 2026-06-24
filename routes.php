<?php

use function Hoo\WordPressPlugin\{
    route,
    action,
    controller,
};

return route()
    ->rest('wordpress-plugin', 'test.json', action(RegisterFeed::class))
    ->rest('wordpress-plugin', 'test.json',  controller(ContentController::class)->append(...), 20);