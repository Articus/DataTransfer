<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataProvider\Factory;

use Articus\DataTransfer\MetadataCache;
use Articus\DataTransfer\MetadataProvider;
use Articus\PluginManager\ConfigAwareFactoryTrait;
use Articus\PluginManager\ServiceFactoryInterface;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use function is_array;
use function is_string;

/**
 * Default factory for MetadataProvider\Annotation
 * @see MetadataProvider\Annotation
 */
class Annotation implements ServiceFactoryInterface
{
	use ConfigAwareFactoryTrait;
	public function __construct(string $configKey = MetadataProvider\Annotation::class)
	{
		$this->configKey = $configKey;
	}

	public function __invoke(ContainerInterface $container, string $name): MetadataProvider\Annotation
	{
		$config = $this->getServiceConfig($container);
		$cache = $this->getMetadataCache($container, $config['cache'] ?? null);
		return new MetadataProvider\Annotation($cache);
	}

	protected function getMetadataCache(ContainerInterface $container, $options): MetadataCache\MetadataCacheInterface
	{
		$result = null;
		switch (true)
		{
			case ($options === null):
			case is_array($options):
				$result = new MetadataCache\FilePerClass($options['directory'] ?? null);
				break;
			case (is_string($options) && $container->has($options)):
				$psr16Cache = $container->get($options);
				if (!($psr16Cache instanceof CacheInterface))
				{
					throw new LogicException('Invalid metadata provider cache service for DataTransfer.');
				}
				$result = new MetadataCache\Psr16($psr16Cache);
				break;
			default:
				throw new LogicException('Invalid configuration for DataTransfer metadata provider cache.');
		}
		return $result;
	}
}
