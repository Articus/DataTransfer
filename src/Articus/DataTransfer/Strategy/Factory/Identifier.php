<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\DataTransfer\Strategy;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory Strategy\Identifier
 * @see Strategy\Identifier
 */
class Identifier implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new \LogicException('Option "type" is required');
		}

		return new Strategy\Identifier($this->getValueLoader($container), $type);
	}

	protected function getValueLoader(ContainerInterface $container): IdentifiableValueLoader
	{
		return $container->get(IdentifiableValueLoader::class);
	}
}
