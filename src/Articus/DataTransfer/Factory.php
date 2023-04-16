<?php
declare(strict_types=1);

namespace Articus\DataTransfer;

use Articus\PluginManager\ConfigAwareFactoryTrait;
use Articus\PluginManager\ServiceFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Default factory for Service
 * @see Service
 */
class Factory implements ServiceFactoryInterface
{
	use ConfigAwareFactoryTrait;

	public function __construct(string $configKey = Service::class)
	{
		$this->configKey = $configKey;
	}

	public function __invoke(ContainerInterface $container, string $name): Service
	{
		$options = new Options($this->getServiceConfig($container));
		return new Service(
			$container->get($options->metadataProvider),
			$container->get($options->strategyPluginManager),
			$container->get($options->validatorPluginManager)
		);
	}
}
