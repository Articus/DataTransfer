<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Exception;
use Articus\DataTransfer\Utility;
use Articus\DataTransfer\Validator;

/**
 * Configurable strategy to deal with maps of complex identifiable values.
 * "Map" means something to iterate over with keys that identify elements - associative array, stdClass or Traversable & ArrayAccess.
 */
class IdentifiableValueMap implements StrategyInterface
{
	/**
	 * Internal strategy to perform data transfer for map items when needed
	 * @var StrategyInterface
	 */
	protected $valueStrategy;

	/**
	 * A way to calculate identifier of typed data for map item
	 * @var callable(mixed): null|string
	 */
	protected $typedValueIdentifier;

	/**
	 * A way to calculate identifier of untyped data for map item
	 * @var callable(mixed): null|string
	 */
	protected $untypedValueIdentifier;

	/**
	 * A way to construct new empty instance of typed data and set it at specific key in map during hydration.
	 * Corresponding untyped data is passed for convenience.
	 * @var null|callable(mixed, int|string, mixed): mixed
	 */
	protected $typedValueSetter;

	/**
	 * A way to remove instance of typed data with specific key from map during hydration.
	 * @var null|callable(mixed, int|string): mixed
	 */
	protected $typedValueRemover;

	/**
	 * A way to construct new empty instance of untyped data during merge.
	 * Corresponding untyped data is passed for convenience.
	 * @var null|callable(mixed): mixed
	 */
	protected $untypedValueConstructor;

	/**
	 * Flag if strategy should extract stdClass instance insteadof associative array
	 * @var bool
	 */
	protected $extractStdClass;

	public function __construct(
		StrategyInterface $valueStrategy,
		callable $typedValueIdentifier,
		callable $untypedValueIdentifier,
		?callable $typedValueSetter,
		?callable $typedValueRemover,
		?callable $untypedValueConstructor,
		bool $extractStdClass
	)
	{
		$this->valueStrategy = $valueStrategy;
		$this->typedValueIdentifier = $typedValueIdentifier;
		$this->untypedValueIdentifier = $untypedValueIdentifier;
		$this->typedValueSetter = $typedValueSetter;
		$this->typedValueRemover = $typedValueRemover;
		$this->untypedValueConstructor = $untypedValueConstructor;
		$this->extractStdClass = $extractStdClass;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		if (!(\is_iterable($from) || ($from instanceof \stdClass)))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Extraction can be done only from iterable or stdClass, not %s',
					\is_object($from) ? \get_class($from) : \gettype($from)
				))
			);
		}
		$result = ($this->extractStdClass ? new \stdClass() : []);
		$map = new Utility\MapAccessor($result);
		foreach ($from as $key => $value)
		{
			try
			{
				$extractedValue = $this->valueStrategy->extract($value);
				$map->set($key, $extractedValue);
			}
			catch (Exception\InvalidData $e)
			{
				$violations = [Validator\Collection::INVALID_INNER => [$key => $e->getViolations()]];
				throw new Exception\InvalidData($violations, $e);
			}
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		if (!(\is_iterable($from) || ($from instanceof \stdClass)))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Hydration can be done only from iterable or stdClass, not %s',
					\is_object($from) ? \get_class($from) : \gettype($from)
				))
			);
		}
		if (!(\is_iterable($to) || ($to instanceof \stdClass)))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Hydration can be done only to iterable or stdClass, not %s',
					\is_object($to) ? \get_class($to) : \gettype($to)
				))
			);
		}
		//Prepare references to destination items
		$toValues = $this->referenceValues($to);
		//Process source items
		$hydratedKeys = [];
		foreach ($from as $fromKey => $fromValue)
		{
			try
			{
				if (\array_key_exists($fromKey, $toValues) && (($this->typedValueIdentifier)($toValues[$fromKey]) === ($this->untypedValueIdentifier)($fromValue)))
				{
					$this->valueStrategy->hydrate($fromValue, $toValues[$fromKey]);
					$hydratedKeys[$fromKey] = true;
				}
				elseif ($this->typedValueSetter !== null)
				{
					$toValue = &($this->typedValueSetter)($to, $fromKey, $fromValue);
					$this->valueStrategy->hydrate($fromValue, $toValue);
					$hydratedKeys[$fromKey] = true;
				}
			}
			catch (Exception\InvalidData $e)
			{
				$violations = [Validator\Collection::INVALID_INNER => [$fromKey => $e->getViolations()]];
				throw new Exception\InvalidData($violations, $e);
			}
		}
		//Remove destination items absent in source
		if ($this->typedValueRemover !== null)
		{
			foreach (\array_keys($toValues) as $toKey)
			{
				if (!($hydratedKeys[$toKey] ?? false))
				{
					($this->typedValueRemover)($to, $toKey);
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function merge($from, &$to): void
	{
		if (!(\is_iterable($from) || ($from instanceof \stdClass)))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Merge can be done only from iterable or stdClass, not %s',
					\is_object($from) ? \get_class($from) : \gettype($from)
				))
			);
		}
		$toMap = new Utility\MapAccessor($to);
		if (!$toMap->accessible())
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Merge can be done only to iterable or stdClass, not %s',
					\is_object($to) ? \get_class($to) : \gettype($to)
				))
			);
		}
		//Prepare references to destination items
		$toValues = $this->referenceValues($to);
		//Process source items
		$mergedKeys = [];
		foreach ($from as $fromKey => $fromValue)
		{
			try
			{
				if (\array_key_exists($fromKey, $toValues) && (($this->untypedValueIdentifier)($toValues[$fromKey]) === ($this->untypedValueIdentifier)($fromValue)))
				{
					$this->valueStrategy->merge($fromValue, $toValues[$fromKey]);
					$mergedKeys[$fromKey] = true;
				}
				elseif ($this->typedValueSetter !== null)
				{
					$toValue = ($this->untypedValueConstructor === null) ? null : ($this->untypedValueConstructor)($fromValue);
					$this->valueStrategy->merge($fromValue, $toValue);
					$toMap->set($fromKey, $toValue);
					$mergedKeys[$fromKey] = true;
				}
			}
			catch (Exception\InvalidData $e)
			{
				$violations = [Validator\Collection::INVALID_INNER => [$fromKey => $e->getViolations()]];
				throw new Exception\InvalidData($violations, $e);
			}
		}
		//Remove destination items absent in source
		if ($this->typedValueRemover !== null)
		{
			foreach (\array_keys($toValues) as $toKey)
			{
				if (!($mergedKeys[$toKey] ?? false))
				{
					$toMap->remove($toKey);
				}
			}
		}
	}
	/**
	 * @param iterable|\stdClass $map
	 * @return array
	 */
	protected function referenceValues(&$map): array
	{
		$result = [];
		foreach ($map as $key => &$value)
		{
			$result[$key] = &$value;
		}
		return $result;
	}
}
