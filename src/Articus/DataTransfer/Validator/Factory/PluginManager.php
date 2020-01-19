<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\ConfigAwareFactory;
use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;

/**
 * Default factory for Validator\PluginManager
 * @see Validator\PluginManager
 */
class PluginManager extends ConfigAwareFactory
{
	public function __construct(string $configKey = Validator\PluginManager::class)
	{
		parent::__construct($configKey);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new Validator\PluginManager($container, $this->getServiceConfig($container));
	}
}
