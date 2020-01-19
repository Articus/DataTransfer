<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\ConfigAwareFactory;
use Articus\DataTransfer\Strategy;
use Interop\Container\ContainerInterface;

/**
 * Default factory for Strategy\PluginManager
 * @see Strategy\PluginManager
 */
class PluginManager extends ConfigAwareFactory
{
	public function __construct(string $configKey = Strategy\PluginManager::class)
	{
		parent::__construct($configKey);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new Strategy\PluginManager($container, $this->getServiceConfig($container));
	}
}
