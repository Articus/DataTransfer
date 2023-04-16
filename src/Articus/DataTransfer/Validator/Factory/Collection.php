<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\Options as DTOptions;
use Articus\DataTransfer\Validator;
use Articus\DataTransfer\Validator\Options;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Default factory for Validator\Collection
 * @see Validator\Collection
 */
class Collection implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Validator\Collection
	{
		$parsedOptions = new Options\Collection($options);
		$itemValidator = $this->getValidatorManager($container)(Validator\Chain::class, ['links' => $parsedOptions->validators]);
		return new Validator\Collection($itemValidator);
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
