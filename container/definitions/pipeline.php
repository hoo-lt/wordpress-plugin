<?php

use Hoo\WordPressPluginFramework\Pipeline;

use function DI\autowire;

return [
	Pipeline\PipelineInterface::class => autowire(Pipeline\Pipeline::class),

	Pipeline\Middlewares\MiddlewaresBuilderInterface::class => autowire(Pipeline\Middlewares\MiddlewaresBuilder::class),

	Pipeline\Middlewares\LogExecutionTime\MiddlewareFactoryInterface::class => autowire(Pipeline\Middlewares\LogExecutionTime\MiddlewareFactory::class),
	Pipeline\Middlewares\Transaction\MiddlewareFactoryInterface::class => autowire(Pipeline\Middlewares\Transaction\MiddlewareFactory::class),

	Pipeline\Middlewares\Validate\Validators\ValidatorsBuilderInterface::class => autowire(Pipeline\Middlewares\Validate\Validators\ValidatorsBuilder::class),
	Pipeline\Middlewares\Validate\Validators\Rule\ValidatorFactoryInterface::class => autowire(Pipeline\Middlewares\Validate\Validators\Rule\ValidatorFactory::class),
	Pipeline\Middlewares\Validate\Validators\Rule\Rules\RulesBuilderInterface::class => autowire(Pipeline\Middlewares\Validate\Validators\Rule\Rules\RulesBuilder::class),
	Pipeline\Middlewares\Validate\Validators\Comparison\ValidatorBuilderInterface::class => autowire(Pipeline\Middlewares\Validate\Validators\Comparison\ValidatorBuilder::class),
	Pipeline\Middlewares\Validate\Validators\Comparison\Comparators\DateTime\ComparatorFactoryInterface::class => autowire(Pipeline\Middlewares\Validate\Validators\Comparison\Comparators\DateTime\ComparatorFactory::class)
		->constructorParameter('format', 'Y-m-d H:i:s'),
];
