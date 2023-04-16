<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataProvider\Factory;

use Articus\DataTransfer\Cache;
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

	protected function getCache(ContainerInterface $container, $options): CacheInterface
	{
		$result = null;
		switch (true)
		{
			case ($options === null):
			case is_array($options):
				$result = new Cache\MetadataFilePerClass($options['directory'] ?? null);
				break;
			case (is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof CacheInterface))
				{
					throw new LogicException('Invalid metadata provider cache service for DataTransfer.');
				}
				break;
			default:
				throw new LogicException('Invalid configuration for DataTransfer metadata provider cache.');
		}
		return $result;
	}
}
