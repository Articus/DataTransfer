<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\ClassMetadataProviderInterface;
use Articus\DataTransfer\Options as DTOptions;
use Articus\DataTransfer\Strategy;
use Articus\DataTransfer\Strategy\Options;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;
use function array_search;

/**
 * Strategy factory for lists of objects that have specific type which can be constructed without arguments.
 * "List" means something to iterate over but without keys that identify elements - indexed array or Traversable.
 */
class NoArgObjectList implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Strategy\IdentifiableValue
	{
		$parsedOptions = new Options\NoArgObjectList($options);
		$valueStrategy = $this->getStrategyManager($container)(
			...$this->getMetadataProvider($container)->getClassStrategy($parsedOptions->type, $parsedOptions->subset)
		);
		$nullIdentifier = static function ($value): ?string
		{
			return null;
		};
		$typedValueAdder = static function &(array &$list, $untypedValue) use ($parsedOptions)
		{
			$defaultValue = new $parsedOptions->type();
			$list[] = &$defaultValue;
			return $defaultValue;
		};
		$typedValueRemover = static function (array &$list, $typedValue): void
		{
			$index = array_search($typedValue, $list, true);
			if ($index !== false)
			{
				unset($list[$index]);
			}
		};
		$untypedValueConstructor = static function ($value) use ($parsedOptions, $valueStrategy)
		{
			$defaultValue = new $parsedOptions->type();
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

	protected function getStrategyManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DTOptions::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
