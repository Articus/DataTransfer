<?php
declare(strict_types=1);

namespace Articus\DataTransfer;

use Generator;
use LogicException;
use function iterator_to_array;
use function sprintf;

/**
 * Service for efficient loading of identifiable values
 */
class IdentifiableValueLoader
{
	/**
	 * Identifiable values that have already been loaded
	 * @var array<string, array<int|string, object|resource|array|string|int|float|bool>> map "type" -> "identifier" -> "value"
	 */
	protected array $values = [];

	/**
	 * Identifiers for missing identifiable values (for which loading has failed)
	 * @var array<string, array<int|string, false>> map "type" -> "identifier" -> false
	 */
	protected array $unknowns = [];

	/**
	 * Identifiers for "preordered" identifiable values (for bulk loading)
	 * @var array<string, array<int|string, false>> map "type" -> "identifier" -> false
	 */
	protected array $wishes = [];

	/**
	 * Registry of supported identifiable value types
	 * @var array<string, array<callable>> map of tuples "type" -> ("identifier getter", "value loader")
	 * @psalm-var array<string, array{0:callable(object|resource|array|string|int|float|bool):int|string|null, 1:callable(array<int|string>):iterable<object|resource|array|string|int|float|bool>}>
	 */
	protected array $types;

	/**
	 * @param array<string, array<callable>> $types map of tuples "type" -> ("identifier getter", "value loader")
	 * @psalm-param array<string, array{0:callable(object|resource|array|string|int|float|bool):int|string|null, 1:callable(array<int|string>):iterable<object|resource|array|string|int|float|bool>}> $types
	 */
	public function __construct(array $types)
	{
		//TODO add strict types structure validation?
		$this->types = $types;
	}

	/**
	 * Returns value identifier
	 * @param string $type
	 * @param object|resource|array|string|int|float|bool $value
	 * @return int|string|null
	 */
	public function identify(string $type, $value)
	{
		[$identifierGetter] = $this->types[$type] ?? null;
		if ($identifierGetter === null)
		{
			throw new LogicException(sprintf('Unknown type "%s"', $type));
		}
		return $identifierGetter($value);
	}

	/**
	 * Stores identifiable value that has already been loaded externally
	 * @param string $type
	 * @param object|resource|array|string|int|float|bool $value
	 */
	public function bank(string $type, $value): void
	{
		$id = $this->identify($type, $value);
		if ($id === null)
		{
			throw new LogicException('Banked value does not have identifier');
		}
		$this->values[$type][$id] = $value;
		unset($this->unknowns[$type][$id]);
		unset($this->wishes[$type][$id]);
	}

	/**
	 * "Preorders" identifiable value for bulk loading
	 * @param string $type
	 * @param int|string $id
	 */
	public function wish(string $type, $id): void
	{
		$this->wishMultiple($type, [$id]);
	}

	/**
	 * "Preorders" several identifiable values for bulk loading
	 * @param string $type
	 * @param array<int|string> $ids
	 */
	public function wishMultiple(string $type, array $ids): void
	{
		foreach ($ids as $id)
		{
			if ($this->unknowns[$type][$id] ?? true)
			{
				$value = $this->values[$type][$id] ?? null;
				if ($value === null)
				{
					$this->wishes[$type][$id] = false;
				}
			}
		}
	}

	/**
	 * Returns identifiable value or null if loading failed
	 * @param string $type
	 * @param int|string $id
	 * @return mixed
	 */
	public function get(string $type, $id)
	{
		return iterator_to_array($this->getMultiple($type, [$id]))[$id] ?? null;
	}

	/**
	 * Emits identifiable values.
	 * Emitted value order might differ from specified identifier order.
	 * There might be less emitted values than specified identifiers if loading failed for some identifiers.
	 * @param string $type
	 * @param array<int|string> $ids
	 * @return Generator<int|string, object|resource|array|string|int|float|bool>
	 */
	public function getMultiple(string $type, array $ids): Generator
	{
		/** @var false[] $unknowns */
		$unknowns = [];
		//Yield previously loaded values and gather identifiers for bulk load
		foreach ($ids as $id)
		{
			if ($this->unknowns[$type][$id] ?? true)
			{
				$value = $this->values[$type][$id] ?? null;
				if ($value === null)
				{
					$unknowns[$id] = false;
				}
				else
				{
					yield $id => $value;
				}
			}
		}
		//Bulk load values that have not been loaded before
		if (!empty($unknowns))
		{
			$wishes = $this->wishes[$type] ?? [];
			unset($this->wishes[$type]);
			foreach ($this->load($type, array_keys($unknowns + $wishes)) as $id => $value)
			{
				$this->values[$type][$id] = $value;
				//Skip "bonus" values
				if (isset($unknowns[$id]))
				{
					yield $id => $value;
					unset($unknowns[$id]);
				}
				unset($wishes[$id]);
			}
			$this->unknowns[$type] = ($this->unknowns[$type] ?? []) + $unknowns + $wishes;
		}
	}

	/**
	 * Loads and emits identifiable values.
	 * Emitted value order might differ from specified identifier order.
	 * There might be less emitted values than specified identifiers if loading failed for some identifiers.
	 * There might be more emitted values than specified identifiers if value loader returns "bonus" values for specified identifiers.
	 * @param string $type
	 * @param array<int|string> $ids
	 * @return Generator<int, object|resource|array|string|int|float|bool>
	 */
	protected function load(string $type, array $ids): Generator
	{
		[$identifierGetter, $valueLoader] = $this->types[$type] ?? null;
		if (($identifierGetter === null) || ($valueLoader === null))
		{
			throw new LogicException(sprintf('Unknown type "%s"', $type));
		}
		foreach ($valueLoader($ids) as $value)
		{
			$id = $identifierGetter($value);
			if ($id === null)
			{
				throw new LogicException('Loaded value does not have identifier');
			}
			yield $id => $value;
		}
	}

	/**
	 * Forgets identifiable value that was wished or loaded
	 * @param string $type
	 * @param int|string|null $id
	 */
	public function forget(string $type, $id = null): void
	{
		if ($id === null)
		{
			unset($this->values[$type]);
			unset($this->unknowns[$type]);
			unset($this->wishes[$type]);
		}
		else
		{
			unset($this->values[$type][$id]);
			unset($this->unknowns[$type][$id]);
			unset($this->wishes[$type][$id]);
		}
	}

	/**
	 * Forgets all identifiable values that were wished or loaded
	 */
	public function forgetAll(): void
	{
		$this->values = [];
		$this->unknowns = [];
		$this->wishes = [];
	}
}
