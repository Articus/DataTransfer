<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\Validator\ValidatorPluginManager;

/**
 * Abstract factory that allows to use Laminas validators via Validator\PluginManager
 * @see Validator\Laminas
 * @see Validator\PluginManager
 * @see ValidatorPluginManager
 */
class Laminas implements AbstractFactoryInterface
{
	public function canCreate(ContainerInterface $container, $requestedName)
	{
		return $this->getLaminasValidatorPluginManager($container)->has($requestedName);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$laminasValidator = $this->getLaminasValidatorPluginManager($container)->get($requestedName, $options);
		return new Validator\Laminas($laminasValidator);
	}

	protected function getLaminasValidatorPluginManager(ContainerInterface $container): ValidatorPluginManager
	{
		return $container->get(ValidatorPluginManager::class);
	}
}
