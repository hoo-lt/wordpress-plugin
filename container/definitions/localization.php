<?php

use Hoo\WordPressPluginFramework\Localization;

use function DI\autowire;

return [
	Localization\Formatter\FormatterInterface::class => autowire(Localization\Formatter\Formatter::class),
	Localization\Translator\TranslatorInterface::class => autowire(Localization\Translator\Translator::class)
		->constructorParameter('domain', 'wordpress-plugin'),
];
