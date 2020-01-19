<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory for Validator\Chain
 * @see Validator\Chain
 */
class Chain implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$linkDeclarations = $options['links'] ?? [];
		$validatorManager = $this->getValidatorManager($container);
		$links = [];
		foreach ($linkDeclarations as [$validatorName, $validatorOptions, $blocker])
		{
			$links[] = [$validatorManager->get($validatorName, $validatorOptions), $blocker];
		}
		return new Validator\Chain($links);
	}

	protected function getValidatorManager(ContainerInterface $container): Validator\PluginManager
	{
		return $container->get(Validator\PluginManager::class);
	}
}
