<?php

use Hoo\WordPressPluginFramework\Http;

use function DI\{
	autowire,
	factory,
};

return [

	/**
	 * Coders
	 */
	Http\Coders\CoderFactoryInterface::class => autowire(Http\Coders\CoderFactory::class),
	Http\Coders\Form\CoderInterface::class => autowire(Http\Coders\Form\Coder::class),
	Http\Coders\Json\CoderInterface::class => autowire(Http\Coders\Json\Coder::class),
	Http\Coders\Query\CoderInterface::class => autowire(Http\Coders\Query\Coder::class),

	/**
	 * Url
	 */
	Http\Url\UrlFactoryInterface::class => autowire(Http\Url\UrlFactory::class),
	Http\Url\Query\QueryFactoryInterface::class => autowire(Http\Url\Query\QueryFactory::class),

	/**
	 * Message
	 */
	Http\Message\Headers\HeadersFactoryInterface::class => autowire(Http\Message\Headers\HeadersFactory::class),
	Http\Message\Body\BodyFactoryInterface::class => autowire(Http\Message\Body\BodyFactory::class),

	/**
	 * Server
	 */
	Http\Server\ServerInterface::class => factory(fn() => new Http\Server\Server(file_get_contents('php://input') ?: '', $_SERVER)),
	Http\Server\Request\RequestFactoryInterface::class => autowire(Http\Server\Request\RequestFactory::class),
	Http\Server\Request\Routes\RoutesFactoryInterface::class => autowire(Http\Server\Request\Routes\RoutesFactory::class),
	Http\Server\Response\ResponseFactoryInterface::class => autowire(Http\Server\Response\ResponseFactory::class),
	Http\Server\Request\RequestInterface::class => factory([
		Http\Server\Request\RequestFactoryInterface::class,
		'createFromServer',
	]),

	/**
	 * Client
	 */
	Http\Client\ClientInterface::class => autowire(Http\Client\Client::class),
	Http\Client\Request\RequestFactoryInterface::class => autowire(Http\Client\Request\RequestFactory::class),
	Http\Client\Response\ResponseFactoryInterface::class => autowire(Http\Client\Response\ResponseFactory::class),
];
