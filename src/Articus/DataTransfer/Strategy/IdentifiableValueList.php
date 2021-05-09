<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Exception;
use Articus\DataTransfer\Validator;

/**
 * Configurable strategy to deal with lists of complex identifiable values.
 * "List" means something to iterate over but without keys that identify elements - indexed array or Traversable.
 */
class IdentifiableValueList implements StrategyInterface
{
	const DUPLICATE_ID = 'duplicateId';

	/**
	 * Internal strategy to perform data transfer for list items when needed
	 * @var StrategyInterface
	 */
	protected $valueStrategy;

	/**
	 * A way to calculate identifier of typed data for list item
	 * @var callable(mixed): null|string
	 */
	protected $typedValueIdentifier;

	/**
	 * A way to calculate identifier of untyped data for list item
	 * @var callable(mixed): null|string
	 */
	protected $untypedValueIdentifier;

	/**
	 * A way to construct new empty instance of typed data and add it to list during hydration.
	 * Corresponding untyped data is passed for convenience.
	 * @var null|callable(mixed, mixed): mixed
	 */
	protected $typedValueAdder;

	/**
	 * A way to remove specific instance of typed data from list during hydration.
	 * @var null|callable(mixed, mixed): mixed
	 */
	protected $typedValueRemover;

	/**
	 * A way to construct new empty instance of untyped data during merge.
	 * Corresponding untyped data is passed for convenience.
	 * @var null|callable(mixed): mixed
	 */
	protected $untypedValueConstructor;

	public function __construct(
		StrategyInterface $valueStrategy,
		callable $typedValueIdentifier,
		callable $untypedValueIdentifier,
		?callable $typedValueAdder,
		?callable $typedValueRemover,
		?callable $untypedValueConstructor
	)
	{
		$this->valueStrategy = $valueStrategy;
		$this->typedValueIdentifier = $typedValueIdentifier;
		$this->untypedValueIdentifier = $untypedValueIdentifier;
		$this->typedValueAdder = $typedValueAdder;
		$this->typedValueRemover = $typedValueRemover;
		$this->untypedValueConstructor = $untypedValueConstructor;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		if (!\is_iterable($from))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Extraction can be done only from iterable, not %s',
					\is_object($from) ? \get_class($from) : \gettype($from)
				))
			);
		}
		$result = [];
		foreach ($from as $index => $value)
		{
			try
			{
				$result[] = $this->valueStrategy->extract($value);
			}
			catch (Exception\InvalidData $e)
			{
				$violations = [Validator\Collection::INVALID_INNER => [$index => $e->getViolations()]];
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
		if (!\is_iterable($from))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Hydration can be done only from iterable, not %s',
					\is_object($from) ? \get_class($from) : \gettype($from)
				))
			);
		}
		if (!\is_iterable($to))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Hydration can be done only to iterable, not %s',
					\is_object($to) ? \get_class($to) : \gettype($to)
				))
			);
		}
		//Prepare references to destination items
		[$toIdentifiedValues, $toUnidentifiedValues] = $this->identifyValues($to, $this->typedValueIdentifier);
		//Process source items
		$fromValueIdToIndexMap = [];
		foreach ($from as $fromIndex => $fromValue)
		{
			try
			{
				$fromValueId = ($this->untypedValueIdentifier)($fromValue);
				if ($fromValueId !== null)
				{
					if (isset($fromValueIdToIndexMap[$fromValueId]))
					{
						$violations = [
							self::DUPLICATE_ID => \sprintf('Same identifier as item %s', $fromValueIdToIndexMap[$fromValueId])
						];
						throw new Exception\InvalidData($violations);
					}
					$fromValueIdToIndexMap[$fromValueId] = $fromIndex;
				}
				if (($fromValueId !== null) && \array_key_exists($fromValueId, $toIdentifiedValues))
				{
					$this->valueStrategy->hydrate($fromValue, $toIdentifiedValues[$fromValueId]);
				}
				elseif ($this->typedValueAdder !== null)
				{
					$toValue = &($this->typedValueAdder)($to, $fromValue);
					$this->valueStrategy->hydrate($fromValue, $toValue);
				}
			}
			catch (Exception\InvalidData $e)
			{
				$violations = [Validator\Collection::INVALID_INNER => [$fromIndex => $e->getViolations()]];
				throw new Exception\InvalidData($violations, $e);
			}
		}
		//Remove destination items absent in source
		if ($this->typedValueRemover !== null)
		{
			foreach ($toIdentifiedValues as $toValueId => &$toValue)
			{
				if (!isset($fromValueIdToIndexMap[$toValueId]))
				{
					($this->typedValueRemover)($to, $toValue);
				}
			}
			foreach ($toUnidentifiedValues as &$toValue)
			{
				($this->typedValueRemover)($to, $toValue);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function merge($from, &$to): void
	{
		if (!\is_iterable($from))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Merge can be done only from iterable, not %s',
					\is_object($from) ? \get_class($from) : \gettype($from)
				))
			);
		}
		if (!\is_iterable($to))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new \InvalidArgumentException(\sprintf(
					'Merge can be done only to iterable, not %s',
					\is_object($to) ? \get_class($to) : \gettype($to)
				))
			);
		}
		//Prepare references to destination items
		[$toIdentifiedValues, $toUnidentifiedValues] = $this->identifyValues($to, $this->untypedValueIdentifier);
		//Process source items
		$to = [];
		$fromValueIdToIndexMap = [];
		foreach ($from as $fromIndex => $fromValue)
		{
			try
			{
				$fromValueId = ($this->untypedValueIdentifier)($fromValue);
				if ($fromValueId !== null)
				{
					if (isset($fromValueIdToIndexMap[$fromValueId]))
					{
						$violations = [
							self::DUPLICATE_ID => \sprintf('Same identifier as item %s', $fromValueIdToIndexMap[$fromValueId])
						];
						throw new Exception\InvalidData($violations);
					}
					$fromValueIdToIndexMap[$fromValueId] = $fromIndex;
				}
				if (($fromValueId !== null) && \array_key_exists($fromValueId, $toIdentifiedValues))
				{
					$to[$fromIndex] = &$toIdentifiedValues[$fromValueId];
					$this->valueStrategy->merge($fromValue, $to[$fromIndex]);
				}
				elseif ($this->typedValueAdder !== null)
				{
					$to[$fromIndex] = ($this->untypedValueConstructor === null) ? null : ($this->untypedValueConstructor)($fromValue);
					$this->valueStrategy->merge($fromValue, $to[$fromIndex]);
				}
			}
			catch (Exception\InvalidData $e)
			{
				$violations = [Validator\Collection::INVALID_INNER => [$fromIndex => $e->getViolations()]];
				throw new Exception\InvalidData($violations, $e);
			}
		}
		//Add destination items absent in source
		if ($this->typedValueRemover === null)
		{
			foreach ($toIdentifiedValues as $toValueId => &$toValue)
			{
				if (!isset($fromValueIdToIndexMap[$toValueId]))
				{
					$to[] = &$toValue;
				}
			}
			foreach ($toUnidentifiedValues as &$toValue)
			{
				$to[] = &$toValue;
			}
		}
	}

	protected function identifyValues(iterable &$list, callable $identifier): array
	{
		$identifiedValues = [];
		$unidentifiedValues = [];
		foreach ($list as &$value)
		{
			$valueId = $identifier($value);
			if ($valueId === null)
			{
				$unidentifiedValues[] = &$value;
			}
			elseif (\array_key_exists($valueId, $identifiedValues))
			{
				throw new Exception\InvalidData(
					Exception\InvalidData::DEFAULT_VIOLATION,
					new \InvalidArgumentException('List contains items with same identifier')
				);
			}
			else
			{
				$identifiedValues[$valueId] = &$value;
			}
		}
		return [$identifiedValues, $unidentifiedValues];
	}
}
