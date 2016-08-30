<?php
namespace Articus\DataTransfer;

use Interop\Container\ContainerInterface;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Validator\ValidatorPluginManager;

class ServiceFactory implements FactoryInterface
{
	//Root configuration key for service
	const CONFIG_KEY = 'data_transfer';

	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = $container->get('config');
		$options = new Options(isset($config[self::CONFIG_KEY])? $config[self::CONFIG_KEY] : []);

		//Prepare metadata cache storage
		$metadataCacheStorage = null;
		switch (true)
		{
			case empty($options->getMetadataCache()):
				throw new \LogicException('DataTransfer metadata cache storage is not configured.');
			case is_array($options->getMetadataCache()):
				$metadataCacheStorage = StorageFactory::factory($options->getMetadataCache());
				break;
			case (is_string($options->getMetadataCache()) && $container->has($options->getMetadataCache())):
				$metadataCacheStorage = $container->get($options->getMetadataCache());
				break;
			default:
				throw new \LogicException('Invalid configuration for DataTransfer metadata cache storage.');
		}

		//Prepare strategy plugin manager
		$strategyPluginManager = null;
		switch (true)
		{
			case is_array($options->getStrategies()):
				$strategyPluginManager = new Strategy\PluginManager($container, $options->getStrategies());
				break;
			case (is_string($options->getStrategies()) && $container->has($options->getStrategies())):
				$strategyPluginManager = $container->get($options->getStrategies());
				break;
			default:
				$strategyPluginManager = new Strategy\PluginManager($container);
				break;
		}

		//Prepare validator plugin manager
		$validatorPluginManager = null;
		switch (true)
		{
			case is_array($options->getValidators()):
				$validatorPluginManager = new ValidatorPluginManager($container, $options->getValidators());
				break;
			case (is_string($options->getValidators()) && $container->has($options->getValidators())):
				$validatorPluginManager = $container->get($options->getValidators());
				break;
			default:
				$validatorPluginManager = new ValidatorPluginManager($container);
				break;
		}

		return new Service($metadataCacheStorage, $strategyPluginManager, $validatorPluginManager);
	}
}