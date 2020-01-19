<?php
declare(strict_types=1);

namespace Articus\DataTransfer;

interface ClassMetadataProviderInterface
{
	/**
	 * Returns strategy declaration from specified metadata subset of specified class
	 * @param string $className
	 * @param string $subset
	 * @return array tuple (<strategy name>, <strategy options>) for Strategy\PluginManager
	 * @psalm-return array{0: string, 1: null|array}
	 */
	public function getClassStrategy(string $className, string $subset): array;

	/**
	 * Returns validator declaration from specified metadata subset of specified class
	 * @param string $className
	 * @param string $subset
	 * @return array tuple (<validator name>, <validator options>) for Validator\PluginManager
	 * @psalm-return array{0: string, 1: null|array}
	 */
	public function getClassValidator(string $className, string $subset): array;
}
