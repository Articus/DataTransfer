<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\DataTransfer\Validator;
use Articus\DataTransfer\Validator\Options;
use Articus\PluginManager\PluginFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Default factory Validator\Identifier
 * @see Validator\Identifier
 */
class Identifier implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Validator\Identifier
	{
		$parseOptions = new Options\Identifier($options);
		return new Validator\Identifier($this->getValueLoader($container), $parseOptions->type);
	}

	protected function getValueLoader(ContainerInterface $container): IdentifiableValueLoader
	{
		return $container->get(IdentifiableValueLoader::class);
	}
}
