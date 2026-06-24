<?php

use Hoo\WordPressPluginFramework\View;

use function DI\autowire;

return [
	View\ViewFactoryInterface::class => autowire(View\ViewFactory::class)
		->constructorParameter('dir', dirname(__DIR__, 2) . '/views'),
	View\Renderer\RendererInterface::class => autowire(View\Renderer\Renderer::class),
	View\Renderer\Escaper\EscaperInterface::class => autowire(View\Renderer\Escaper\Escaper::class),
];
