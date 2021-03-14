<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\ClassMetadataProviderInterface;
use Articus\DataTransfer\Strategy;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Strategy factory for objects that have specific type which can be constructed without arguments.
 */
class NoArgObject implements FactoryInterface
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
		$valueStrategy = $this->getStrategyManager($container)->get(...$this->getMetadataProvider($container)->getClassStrategy($type, $subset));
		$nullIdentifier = static function ($value): ?string
		{
			return null;
		};
		$typedValueConstructor = static function ($untypedValue) use ($type)
		{
			return new $type();
		};
		$untypedValueConstructor = static function ($untypedValue) use ($type, $valueStrategy)
		{
			$defaultValue = new $type();
			return $valueStrategy->extract($defaultValue);
		};
		return new Strategy\IdentifiableValue(
			$valueStrategy,
			$nullIdentifier,
			$nullIdentifier,
			$typedValueConstructor,
			$untypedValueConstructor
		);
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
