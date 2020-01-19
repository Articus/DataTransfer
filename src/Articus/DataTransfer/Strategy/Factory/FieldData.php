<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\FieldMetadataProviderInterface;
use Articus\DataTransfer\Strategy;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Default factory for Strategy\FieldData
 * @see Strategy\FieldData
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
		elseif (!\class_exists($type))
		{
			throw new \LogicException(\sprintf('Type "%s" does not exist', $type));
		}
		$subset = $options['subset'] ?? '';
		$extractStdClass = $options['extract_std_class'] ?? false;
		$metadataProvider = $this->getMetadataProvider($container);
		$strategyManager = $this->getStrategyManager($container);
		$typeFields = [];
		foreach ($metadataProvider->getClassFields($type, $subset) as [$fieldName, $getter, $setter])
		{
			$strategy = $strategyManager->get(...$metadataProvider->getFieldStrategy($type, $subset, $fieldName));
			$typeFields[] = [$fieldName, $getter, $setter, $strategy];
		}
		return new Strategy\FieldData($type, $typeFields, $extractStdClass);
	}

	protected function getMetadataProvider(ContainerInterface $container): FieldMetadataProviderInterface
	{
		return $container->get(FieldMetadataProviderInterface::class);
	}

	protected function getStrategyManager(ContainerInterface $container): Strategy\PluginManager
	{
		return $container->get(Strategy\PluginManager::class);
	}
}
