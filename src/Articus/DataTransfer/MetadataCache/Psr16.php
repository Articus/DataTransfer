<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataCache;

use Psr\SimpleCache\CacheInterface;
use function str_replace;

class Psr16 implements MetadataCacheInterface
{
	protected CacheInterface $cache;

	public function __construct(CacheInterface $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * @inheritDoc
	 */
	public function get(string $className): ?array
	{
		$key = $this->getCacheKey($className);
		return $this->cache->get($key);
	}

	/**
	 * @inheritDoc
	 */
	public function set(string $className, array $metadata): bool
	{
		$key = $this->getCacheKey($className);
		return $this->cache->set($key, $metadata);
	}

	/**
	 * Converts class name to PSR-16 compatible key for cached item
	 * @param class-string $className
	 * @return string
	 */
	protected function getCacheKey(string $className): string
	{
		return str_replace('\\', '.', $className);
	}
}
