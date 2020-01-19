<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\ClassMetadataProviderInterface;
use Articus\DataTransfer\Strategy;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory for Strategy\NoArgObjectList
 * @see Strategy\NoArgObjectList
 */
class NoArgObjectList implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new \LogicException('Option "type" is required');
		}
		elseif (!\class_exists($type))
		{
			throw new \LogicException(\sprintf('Type "%s" does not exist', $type));
		}
		$subset = $options['subset'] ?? '';
		$strategy = $this->getStrategyManager($container)->get(...$this->getMetadataProvider($container)->getClassStrategy($type, $subset));
		return new Strategy\NoArgObjectList($strategy, $type);
	}

	protected function getMetadataProvider(ContainerInterface $container): ClassMetadataProviderInterface
	{
		return $container->get(ClassMetadataProviderInterface::class);
	}

	protected function getStrategyManager(ContainerInterface $container): Strategy\PluginManager
	{
		return $container->get(Strategy\PluginManager::class);
	}
}
