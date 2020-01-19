<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataProvider\Factory;

use Articus\DataTransfer\ConfigAwareFactory;
use Articus\DataTransfer\MetadataProvider;
use Interop\Container\ContainerInterface;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

/**
 * Default factory for MetadataProvider\Annotation
 * @see MetadataProvider\Annotation
 */
class Annotation extends ConfigAwareFactory
{
	protected const DEFAULT_CONFIG = [
		'cache' => [
			'adapter' => 'blackhole',
		],
	];

	public function __construct(string $configKey = MetadataProvider\Annotation::class)
	{
		parent::__construct($configKey);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = \array_merge(self::DEFAULT_CONFIG, $this->getServiceConfig($container), $options ?? []);
		$cacheStorage = $this->getCacheStorage($container, $config['cache']);
		return new MetadataProvider\Annotation($cacheStorage);
	}

	protected function getCacheStorage(ContainerInterface $container, $options): CacheStorage
	{
		$result = null;
		switch (true)
		{
			case empty($options):
				throw new \LogicException('DataTransfer metadata provider cache storage is not configured.');
			case \is_array($options):
				$result = StorageFactory::factory($options);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof CacheStorage))
				{
					throw new \LogicException('Invalid metadata provider cache storage for DataTransfer.');
				}
				break;
			default:
				throw new \LogicException('Invalid configuration for DataTransfer metadata provider cache storage.');
		}
		return $result;
	}
}
