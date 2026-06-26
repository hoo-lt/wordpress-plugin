<?php

use function Hoo\WordPressPlugin\{
    hook,
    action,
    controller,
};

return hook()
    ->action('init', action(RegisterFeed::class))
    ->filter('the_content', controller(ContentController::class, 'append'), 20);