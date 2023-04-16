<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\ClassMetadataProviderInterface;
use Articus\DataTransfer\Options as DTOptions;
use Articus\DataTransfer\Validator;
use Articus\DataTransfer\Validator\Options;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Default factory for Validator\TypeCompliant
 * @see Validator\TypeCompliant
 */
class TypeCompliant implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Validator\TypeCompliant
	{
		$parsedOptions = new Options\TypeCompliant($options);
		$typeValidator = $this->getValidatorManager($container)(
			...$this->getMetadataProvider($container)->getClassValidator($parsedOptions->type, $parsedOptions->subset)
		);
		return new Validator\TypeCompliant($typeValidator);
	}

	protected function getMetadataProvider(ContainerInterface $container): ClassMetadataProviderInterface
	{
		return $container->get(ClassMetadataProviderInterface::class);
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
