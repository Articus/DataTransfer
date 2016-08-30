<?php

namespace Articus\DataTransfer\Strategy;
use Articus\DataTransfer\Service;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;

class Factory implements FactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$dataTransferService = $container->get(Service::class);
		$result = null;
		switch ($requestedName)
		{
			case EmbeddedObject::class:
				$result = new EmbeddedObject($options);
				break;
			case EmbeddedObjectArray::class:
				$result = new EmbeddedObjectArray($options);
				break;
			default:
				throw new ServiceNotCreatedException(sprintf('Unable to create strategy %s.', $requestedName));
		}
		$result->setDataTransferService($dataTransferService);
		return $result;
	}

}