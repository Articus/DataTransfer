<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

class FactorySpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		DT\Strategy\PluginManager $strategyManager,
		DT\Validator\PluginManager $validatorManager
	)
	{
		$container->get(DT\ClassMetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(DT\Strategy\PluginManager::class)->shouldBeCalledOnce()->willReturn($strategyManager);
		$container->get(DT\Validator\PluginManager::class)->shouldBeCalledOnce()->willReturn($validatorManager);

		$this->__invoke($container, 'test')->shouldBeAnInstanceOf(DT\Service::class);
	}
}
