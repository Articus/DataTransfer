<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\FieldMetadataProviderInterface;
use Articus\DataTransfer\Options as DTOptions;
use Articus\DataTransfer\Strategy;
use Articus\DataTransfer\Strategy\Options;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Default factory for Strategy\FieldData
 * @see Strategy\FieldData
 */
class FieldData implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Strategy\FieldData
	{
		$parsedOptions = new Options\FieldData($options);
		$metadataProvider = $this->getMetadataProvider($container);
		$strategyManager = $this->getStrategyManager($container);
		$typeFields = [];
		foreach ($metadataProvider->getClassFields($parsedOptions->type, $parsedOptions->subset) as [$fieldName, $getter, $setter])
		{
			$strategy = $strategyManager(...$metadataProvider->getFieldStrategy($parsedOptions->type, $parsedOptions->subset, $fieldName));
			$typeFields[] = [$fieldName, $getter, $setter, $strategy];
		}
		return new Strategy\FieldData($parsedOptions->type, $typeFields, $parsedOptions->extractStdClass);
	}

	protected function getMetadataProvider(ContainerInterface $container): FieldMetadataProviderInterface
	{
		return $container->get(FieldMetadataProviderInterface::class);
	}

	protected function getStrategyManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DTOptions::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}
}
