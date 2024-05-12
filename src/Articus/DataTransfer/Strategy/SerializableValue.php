<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Exception;
use InvalidArgumentException;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Configurable strategy to deal with serialized values.
 */
class SerializableValue implements StrategyInterface
{
	/**
	 * Internal strategy to perform data transfer for unserialized values
	 */
	protected StrategyInterface $valueStrategy;

	/**
	 * A way to encode typed data to string
	 * @var callable(mixed): string
	 */
	protected $serializer;

	/**
	 * A way to decode typed data from string
	 * @var callable(string): mixed
	 */
	protected $unserializer;

	public function __construct(StrategyInterface $valueStrategy, callable $serializer, callable $unserializer)
	{
		$this->valueStrategy = $valueStrategy;
		$this->serializer = $serializer;
		$this->unserializer = $unserializer;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		return ($from === null) ? null : ($this->serializer)($this->valueStrategy->extract($from));
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		if ($from === null)
		{
			$to = null;
		}
		elseif (!is_string($from))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new InvalidArgumentException(sprintf(
					'Hydration can be done only from string, not %s',
					is_object($from) ? get_class($from) : gettype($from)
				))
			);
		}
		else
		{
			$value = ($this->unserializer)($from);
			$this->valueStrategy->hydrate($value, $to);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function merge($from, &$to): void
	{
		if ($from === null)
		{
			$to = null;
		}
		elseif (!is_string($from))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new InvalidArgumentException(sprintf(
					'Merge can be done only from string, not %s',
					is_object($from) ? get_class($from) : gettype($from)
				))
			);
		}
		elseif ($to === null)
		{
			$to = $from;
		}
		elseif (!is_string($to))
		{
			throw new Exception\InvalidData(
				Exception\InvalidData::DEFAULT_VIOLATION,
				new InvalidArgumentException(sprintf(
					'Merge can be done only to string, not %s',
					is_object($to) ? get_class($to) : gettype($to)
				))
			);
		}
		else
		{
			$fromValue = ($this->unserializer)($from);
			$toValue = ($this->unserializer)($to);

			$this->valueStrategy->merge($fromValue, $toValue);
			$to = ($this->serializer)($toValue);
		}
	}
}
