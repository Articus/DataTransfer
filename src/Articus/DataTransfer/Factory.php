<?php
declare(strict_types=1);

namespace Articus\DataTransfer;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory for Service
 * @see Service
 */
class Factory implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$metadataProvider = $this->getMetadataProvider($container);
		$strategyManager = $this->getStrategyManager($container);
		$validatorManager = $this->getValidatorManager($container);
		return new Service($metadataProvider, $strategyManager, $validatorManager);
	}

	protected function getMetadataProvider(ContainerInterface $container): ClassMetadataProviderInterface
	{
		return $container->get(ClassMetadataProviderInterface::class);
	}

	protected function getStrategyManager(ContainerInterface $container): Strategy\PluginManager
	{
		return $container->get(Strategy\PluginManager::class);
	}

	protected function getValidatorManager(ContainerInterface $container): Validator\PluginManager
	{
		return $container->get(Validator\PluginManager::class);
	}
}
