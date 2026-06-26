<?php

namespace Hoo\WordPressPlugin\View;

use Hoo\WordPressPlugin\Application\Application;
use Hoo\WordPressPluginFramework\{
    View\Model\ModelInterface,
    View\ViewInterface,
    View\ViewFactoryInterface,
};

function view(string $view, ?ModelInterface $model = null): ViewInterface
{
    return Application::instance()->container()->get(ViewFactoryInterface::class)->create($view, $model);
}