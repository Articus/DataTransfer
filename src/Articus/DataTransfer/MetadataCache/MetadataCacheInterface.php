<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataCache;

interface MetadataCacheInterface
{
	/**
	 * @param class-string $className
	 * @return null|array
	 */
	public function get(string $className): ?array;

	/**
	 * @param class-string $className
	 * @param array $metadata
	 * @return bool
	 */
	public function set(string $className, array $metadata): bool;
}
