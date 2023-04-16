<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\PluginManager as PM;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class LaminasPluginManagerSpec extends ObjectBehavior
{
	public function it_creates_service(ContainerInterface $container)
	{
		$container->get('config')->shouldBeCalledOnce()->willReturn([]);

		$service = $this->__invoke($container, 'testName');
		$service->shouldBeAnInstanceOf(PM\Laminas::class);
		//TODO how to check enriched default configuration
	}
}
