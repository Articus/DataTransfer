<?php
declare(strict_types=1);

namespace Articus\DataTransfer;

interface FieldMetadataProviderInterface
{
	/**
	 * Returns field declarations from specified metadata subset of specified class
	 * @param class-string $className
	 * @param string $subset
	 * @return iterable tuples (<field name>, <getter declaration>, <setter declaration>)
	 * @psalm-return iterable<array{0: string, 1: null|array{0: string, 1: bool}, 2: null|array{0: string, 1: bool}}>
	 */
	public function getClassFields(string $className, string $subset): iterable;

	/**
	 * Returns strategy declaration for specified field from specified metadata subset of specified class
	 * @param class-string $className
	 * @param string $subset
	 * @param string $fieldName
	 * @return array tuple (<strategy name>, <strategy options>) for Strategy\PluginManager
	 * @psalm-return array{0: string, 1: array}
	 */
	public function getFieldStrategy(string $className, string $subset, string $fieldName): array;

	/**
	 * Returns validator declaration for specified field from specified metadata subset of specified class
	 * @param class-string $className
	 * @param string $subset
	 * @param string $fieldName
	 * @return array tuple (<validator name>, <validator options>) for Validator\PluginManager
	 * @psalm-return array{0: string, 1: array}
	 */
	public function getFieldValidator(string $className, string $subset, string $fieldName): array;
}
