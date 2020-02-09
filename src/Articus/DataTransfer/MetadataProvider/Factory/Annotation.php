<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataProvider\Factory;

use Articus\DataTransfer\ConfigAwareFactory;
use Articus\DataTransfer\MetadataProvider;
use Articus\DataTransfer\Cache;
use Doctrine\Common\Cache\Cache as CacheStorage;
use Doctrine\Common\Cache\VoidCache;
use Interop\Container\ContainerInterface;

/**
 * Default factory for MetadataProvider\Annotation
 * @see MetadataProvider\Annotation
 */
class Annotation extends ConfigAwareFactory
{
	public function __construct(string $configKey = MetadataProvider\Annotation::class)
	{
		parent::__construct($configKey);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = \array_merge($this->getServiceConfig($container), $options ?? []);
		$cacheStorage = $this->getCacheStorage($container, $config['cache'] ?? null);
		return new MetadataProvider\Annotation($cacheStorage);
	}

	protected function getCacheStorage(ContainerInterface $container, $options): CacheStorage
	{
		$result = null;
		switch (true)
		{
			case ($options === null):
				$result = new VoidCache();
				break;
			case \is_array($options):
				$result = new Cache\Annotation($options['directory'] ?? '');
				$result->setNamespace($this->configKey);
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
