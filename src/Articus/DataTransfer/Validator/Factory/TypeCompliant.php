<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\ClassMetadataProviderInterface;
use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory for Validator\TypeCompliant
 * @see Validator\TypeCompliant
 */
class TypeCompliant implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new \LogicException('Option "type" is required');
		}
		$subset = $options['subset'] ?? '';
		$typeValidator = $this->getValidatorManager($container)->get(...$this->getMetadataProvider($container)->getClassValidator($type, $subset));
		return new Validator\TypeCompliant($typeValidator);
	}

	protected function getMetadataProvider(ContainerInterface $container): ClassMetadataProviderInterface
	{
		return $container->get(ClassMetadataProviderInterface::class);
	}

	protected function getValidatorManager(ContainerInterface $container): Validator\PluginManager
	{
		return $container->get(Validator\PluginManager::class);
	}
}
