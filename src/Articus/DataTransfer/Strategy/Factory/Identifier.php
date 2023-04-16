<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\DataTransfer\Strategy;
use Articus\DataTransfer\Strategy\Options;
use Articus\PluginManager\PluginFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Default factory Strategy\Identifier
 * @see Strategy\Identifier
 */
class Identifier implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Strategy\Identifier
	{
		$parsedOptions = new Options\Identifier($options);
		return new Strategy\Identifier($this->getValueLoader($container), $parsedOptions->type);
	}

	protected function getValueLoader(ContainerInterface $container): IdentifiableValueLoader
	{
		return $container->get(IdentifiableValueLoader::class);
	}
}
