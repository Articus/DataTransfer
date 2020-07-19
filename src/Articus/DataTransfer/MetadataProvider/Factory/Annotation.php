<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataProvider\Factory;

use Articus\DataTransfer\ConfigAwareFactory;
use Articus\DataTransfer\MetadataProvider;
use Articus\DataTransfer\Cache;
use Interop\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

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
		$cache = $this->getCache($container, $config['cache'] ?? null);
		return new MetadataProvider\Annotation($cache);
	}

	protected function getCache(ContainerInterface $container, $options): CacheInterface
	{
		$result = null;
		switch (true)
		{
			case ($options === null):
			case \is_array($options):
				$result = new Cache\MetadataFilePerClass($options['directory'] ?? null);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof CacheInterface))
				{
					throw new \LogicException('Invalid metadata provider cache service for DataTransfer.');
				}
				break;
			default:
				throw new \LogicException('Invalid configuration for DataTransfer metadata provider cache.');
		}
		return $result;
	}
}
