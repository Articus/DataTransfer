<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\ClassMetadataProviderInterface;
use Articus\DataTransfer\Strategy;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Strategy factory for lists of objects that have specific type which can be constructed without arguments.
 * "List" means something to iterate over but without keys that identify elements - indexed array or Traversable.
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
		$valueStrategy = $this->getStrategyManager($container)->get(...$this->getMetadataProvider($container)->getClassStrategy($type, $subset));
		$nullIdentifier = static function ($value): ?string
		{
			return null;
		};
		$typedValueAdder = static function &(array &$list, $untypedValue) use ($type)
		{
			$defaultValue = new $type();
			$list[] = &$defaultValue;
			return $defaultValue;
		};
		$typedValueRemover = static function (array &$list, $typedValue): void
		{
			$index = \array_search($typedValue, $list, true);
			if ($index !== false)
			{
				unset($list[$index]);
			}
		};
		$untypedValueConstructor = static function ($value) use ($type, $valueStrategy)
		{
			$defaultValue = new $type();
			return $valueStrategy->extract($defaultValue);
		};
		$arrayConstructor = static function ($value): array
		{
			return [];
		};

		return new Strategy\IdentifiableValue(
			new Strategy\IdentifiableValueList(
				$valueStrategy,
				$nullIdentifier,
				$nullIdentifier,
				$typedValueAdder,
				$typedValueRemover,
				$untypedValueConstructor
			),
			$nullIdentifier,
			$nullIdentifier,
			$arrayConstructor,
			$arrayConstructor
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
