<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory Validator\Identifier
 * @see Validator\Identifier
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

		return new Validator\Identifier($this->getValueLoader($container), $type);
	}

	protected function getValueLoader(ContainerInterface $container): IdentifiableValueLoader
	{
		return $container->get(IdentifiableValueLoader::class);
	}
}
