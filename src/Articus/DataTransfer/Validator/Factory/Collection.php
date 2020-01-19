<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory for Validator\Collection
 * @see Validator\Collection
 */
class Collection implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$validators = $options['validators'] ?? [];
		$links = [];
		foreach ($validators as $index => $validator)
		{
			$name = $validator['name'] ?? null;
			if ($name === null)
			{
				throw new \LogicException(\sprintf('Invalid options "validators": no name at index "%s".', $index));
			}
			$links[] = [$name, $validator['options'] ?? null, $validator['blocker'] ?? false];
		}
		$itemValidator = $this->getValidatorManager($container)->get(Validator\Chain::class, ['links' => $links]);
		return new Validator\Collection($itemValidator);
	}

	protected function getValidatorManager(ContainerInterface $container): Validator\PluginManager
	{
		return $container->get(Validator\PluginManager::class);
	}
}
