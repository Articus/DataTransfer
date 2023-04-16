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

/**
 * Strategy factory for objects that have specific type which can be constructed without arguments.
 */
class NoArgObject implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Strategy\IdentifiableValue
	{
		$parsedOptions = new Options\NoArgObject($options);
		$valueStrategy = $this->getStrategyManager($container)(
			...$this->getMetadataProvider($container)->getClassStrategy($parsedOptions->type, $parsedOptions->subset)
		);
		$nullIdentifier = static function ($value): ?string
		{
			return null;
		};
		$typedValueConstructor = static function ($untypedValue) use ($parsedOptions)
		{
			return new $parsedOptions->type();
		};
		$untypedValueConstructor = static function ($untypedValue) use ($parsedOptions, $valueStrategy)
		{
			$defaultValue = new $parsedOptions->type();
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

	protected function getStrategyManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DTOptions::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
