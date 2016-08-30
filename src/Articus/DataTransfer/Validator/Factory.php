<?php

namespace Articus\DataTransfer\Validator;
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
		switch ($requestedName)
		{
			case Dictionary::class:
				$dataTransferService = $container->get(Service::class);
				$result = new Dictionary($options);
				$result->setDataTransferService($dataTransferService);
				break;
			case Collection::class:
				/** @var Service $dataTransferService */
				$dataTransferService = $container->get(Service::class);
				$validatorPluginManager = $dataTransferService->getValidatorPluginManager();
				$result = new Collection($options);
				$result->setValidatorPluginManager($validatorPluginManager);
				break;
			default:
				throw new ServiceNotCreatedException(sprintf('Unable to create validator %s.', $requestedName));
		}
		return $result;
	}

}