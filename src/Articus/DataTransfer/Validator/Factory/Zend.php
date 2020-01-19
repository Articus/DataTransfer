<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\Validator\ValidatorPluginManager;

/**
 * Abstract factory that allows to use Zend validators via Validator\PluginManager
 * @see Validator\Zend
 * @see Validator\PluginManager
 * @see ValidatorPluginManager
 */
class Zend implements AbstractFactoryInterface
{
	public function canCreate(ContainerInterface $container, $requestedName)
	{
		return $this->getZendValidatorPluginManager($container)->has($requestedName);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$zendValidator = $this->getZendValidatorPluginManager($container)->get($requestedName, $options);
		return new Validator\Zend($zendValidator);
	}

	protected function getZendValidatorPluginManager(ContainerInterface $container): ValidatorPluginManager
	{
		return $container->get(ValidatorPluginManager::class);
	}
}
