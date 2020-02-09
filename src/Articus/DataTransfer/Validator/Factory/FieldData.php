<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\FieldMetadataProviderInterface;
use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory for Validator\FieldData
 * @see Validator\FieldData
 */
class FieldData implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$type = $options['type'] ?? null;
		if ($type === null)
		{
			throw new \LogicException('Option "type" is required');
		}
		$subset = $options['subset'] ?? '';
		$metadataProvider = $this->getMetadataProvider($container);
		$validatorManager = $this->getValidatorManager($container);
		$fields = [];
		foreach ($metadataProvider->getClassFields($type, $subset) as [$fieldName, $getter, $setter])
		{
			$validator = $validatorManager->get(...$metadataProvider->getFieldValidator($type, $subset, $fieldName));
			$fields[] = [$fieldName, $validator];
		}
		return new Validator\FieldData($fields);
	}

	protected function getMetadataProvider(ContainerInterface $container): FieldMetadataProviderInterface
	{
		return $container->get(FieldMetadataProviderInterface::class);
	}

	protected function getValidatorManager(ContainerInterface $container): Validator\PluginManager
	{
		return $container->get(Validator\PluginManager::class);
	}
}
