<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\PluginManager as PM;
use Laminas\Validator\ValidatorPluginManager;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class LaminasPluginManagerSpec extends ObjectBehavior
{
	public function it_creates_service(ContainerInterface $container)
	{
		$container->get('config')->shouldBeCalledOnce()->willReturn([]);
		$container->has(ValidatorPluginManager::class)->shouldBeCalledOnce()->willReturn(false);

		$service = $this->__invoke($container, 'testName');
		$service->shouldBeAnInstanceOf(PM\Laminas::class);
		//TODO how to check enriched default configuration
	}
}
