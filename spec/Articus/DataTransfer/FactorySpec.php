<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer;

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class FactorySpec extends ObjectBehavior
{
	public function it_creates_service_with_empty_config(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager
	)
	{
		$container->get('config')->shouldBeCalledOnce()->willReturn([]);
		$container->get(DT\ClassMetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($strategyManager);
		$container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($validatorManager);

		$service = $this->__invoke($container, 'test');
		$service->shouldBeAnInstanceOf(DT\Service::class);
		$service->shouldHaveProperty('metadataProvider', $metadataProvider);
		$service->shouldHaveProperty('strategyManager', $strategyManager);
		$service->shouldHaveProperty('validatorManager', $validatorManager);
	}

	public function it_creates_service_with_custom_metadata_provider(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager
	)
	{
		$serviceName = 'test_service_name';
		$config = [
			DT\Service::class => [
				'metadata_provider' => $serviceName,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get($serviceName)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($strategyManager);
		$container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($validatorManager);

		$service = $this->__invoke($container, 'test');
		$service->shouldBeAnInstanceOf(DT\Service::class);
		$service->shouldHaveProperty('metadataProvider', $metadataProvider);
		$service->shouldHaveProperty('strategyManager', $strategyManager);
		$service->shouldHaveProperty('validatorManager', $validatorManager);
	}

	public function it_creates_service_with_custom_strategy_manager(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager
	)
	{
		$serviceName = 'test_service_name';
		$config = [
			DT\Service::class => [
				'strategy_manager' => $serviceName,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get(DT\ClassMetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get($serviceName)->shouldBeCalledOnce()->willReturn($strategyManager);
		$container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($validatorManager);

		$service = $this->__invoke($container, 'test');
		$service->shouldBeAnInstanceOf(DT\Service::class);
		$service->shouldHaveProperty('metadataProvider', $metadataProvider);
		$service->shouldHaveProperty('strategyManager', $strategyManager);
		$service->shouldHaveProperty('validatorManager', $validatorManager);
	}

	public function it_creates_service_with_custom_validator_manager(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager
	)
	{
		$serviceName = 'test_service_name';
		$config = [
			DT\Service::class => [
				'validator_manager' => $serviceName,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get(DT\ClassMetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($strategyManager);
		$container->get($serviceName)->shouldBeCalledOnce()->willReturn($validatorManager);

		$service = $this->__invoke($container, 'test');
		$service->shouldBeAnInstanceOf(DT\Service::class);
		$service->shouldHaveProperty('metadataProvider', $metadataProvider);
		$service->shouldHaveProperty('strategyManager', $strategyManager);
		$service->shouldHaveProperty('validatorManager', $validatorManager);
	}

	public function it_creates_service_with_config_in_custom_location(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager
	)
	{
		$configKey = 'test_service_config';
		$mpName = 'test_mp';
		$spmName = 'test_spm';
		$vpmName = 'test_vpm';
		$config = [
			$configKey => [
				'metadata_provider' => $mpName,
				'strategy_manager' => $spmName,
				'validator_manager' => $vpmName,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get($mpName)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get($spmName)->shouldBeCalledOnce()->willReturn($strategyManager);
		$container->get($vpmName)->shouldBeCalledOnce()->willReturn($validatorManager);

		$this->beConstructedWith($configKey);

		$service = $this->__invoke($container, 'test');
		$service->shouldBeAnInstanceOf(DT\Service::class);
		$service->shouldHaveProperty('metadataProvider', $metadataProvider);
		$service->shouldHaveProperty('strategyManager', $strategyManager);
		$service->shouldHaveProperty('validatorManager', $validatorManager);
	}
}
