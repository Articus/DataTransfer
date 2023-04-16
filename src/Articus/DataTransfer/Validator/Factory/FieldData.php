<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\FieldMetadataProviderInterface;
use Articus\DataTransfer\Options as DTOptions;
use Articus\DataTransfer\Validator;
use Articus\DataTransfer\Validator\Options;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Default factory for Validator\FieldData
 * @see Validator\FieldData
 */
class FieldData implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Validator\FieldData
	{
		$parsedOptions = new Options\FieldData($options);
		$metadataProvider = $this->getMetadataProvider($container);
		$validatorManager = $this->getValidatorManager($container);
		$fields = [];
		foreach ($metadataProvider->getClassFields($parsedOptions->type, $parsedOptions->subset) as [$fieldName, $getter, $setter])
		{
			$validator = $validatorManager(...$metadataProvider->getFieldValidator($parsedOptions->type, $parsedOptions->subset, $fieldName));
			$fields[] = [$fieldName, $validator];
		}
		return new Validator\FieldData($fields);
	}

	protected function getMetadataProvider(ContainerInterface $container): FieldMetadataProviderInterface
	{
		return $container->get(FieldMetadataProviderInterface::class);
	}

	/**
	 * @param ContainerInterface $container
	 * @return PluginManagerInterface<Validator\ValidatorInterface>
	 */
	protected function getValidatorManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DTOptions::DEFAULT_VALIDATOR_PLUGIN_MANAGER);
	}
}
