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
 * Default factory for MetadataProvider\PhpAttribute
 * @see MetadataProvider\PhpAttribute
 */
class PhpAttribute implements ServiceFactoryInterface
{
	use ConfigAwareFactoryTrait;

	public function __construct(
		protected string $configKey = MetadataProvider\PhpAttribute::class
	)
	{
	}

	public function __invoke(ContainerInterface $container, string $name): MetadataProvider\PhpAttribute
	{
		$config = $this->getServiceConfig($container);
		$cache = $this->getCache($container, $config['cache'] ?? null);
		return new MetadataProvider\PhpAttribute($cache);
	}

	protected function getCache(ContainerInterface $container, $options): MetadataCache\MetadataCacheInterface
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
