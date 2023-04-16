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
 * Default factory for Validator\Chain
 * @see Validator\Chain
 */
class Chain implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Validator\Chain
	{
		$parsedOptions = new Options\Chain($options);
		$validatorManager = $this->getValidatorManager($container);
		$links = [];
		foreach ($parsedOptions->links as $linkOptions)
		{
			$links[] = [$validatorManager($linkOptions->name, $linkOptions->options), $linkOptions->blocker];
		}
		return new Validator\Chain($links);
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
