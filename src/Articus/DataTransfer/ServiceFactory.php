<?php
namespace Articus\DataTransfer;

use Interop\Container\ContainerInterface;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Validator\ValidatorPluginManager;

class ServiceFactory implements FactoryInterface
{
	/**
	 * Key inside Config service
	 * @var string
	 */
	protected $configKey;

	/**
	 * Factory constructor.
	 */
	public function __construct($configKey = Service::class)
	{
		$this->configKey = $configKey;
	}

	/**
	 * Small hack to simplify configuration when you want to pass custom config key but do not want to create extra class or anonymous function.
	 * So for example in your configuration YAML file you can use:
	 * dependencies:
	 *   factories:
	 *     my_service: [My\Service\ConfigAwareFactory, my_service_config]
	 * my_service_config:
	 *   parameter: value
	 */
	public static function __callStatic($name, array $arguments)
	{
		if (count($arguments) < 3)
		{
			throw new \InvalidArgumentException(sprintf(
				'To invoke %s with custom configuration key statically 3 arguments are required: container, service name and options.',
				static::class
			));
		}
		return (new static($name))->__invoke($arguments[0], $arguments[1], $arguments[2]);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = $container->get('config');
		$options = new Options(isset($config[$this->configKey])? $config[$this->configKey] : []);

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

		return new Service(new Metadata\Reader\Annotation($metadataCacheStorage), $strategyPluginManager, $validatorPluginManager);
	}
}