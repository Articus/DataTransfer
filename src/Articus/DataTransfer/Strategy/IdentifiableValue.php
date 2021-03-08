<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

/**
 * Configurable strategy to deal with complex identifiable values according two rules:
 * - usual data transfer occurs if source and destination have same identifier,
 * - destination is constructed anew and usual data transfer occurs if source and destination have distinct identifiers
 */
class IdentifiableValue implements StrategyInterface
{
	/**
	 * Internal strategy to perform data transfer when needed
	 * @var StrategyInterface
	 */
	protected $valueStrategy;

	/**
	 * A way to calculate identifier of typed data
	 * @var callable(mixed): null|string
	 */
	protected $typedValueIdentifier;

	/**
	 * A way to calculate identifier of untyped data
	 * @var callable(mixed): null|string
	 */
	protected $untypedValueIdentifier;

	/**
	 * A way to construct new empty instance of typed data during hydration.
	 * Corresponding untyped data is passed for convenience.
	 * @var callable(mixed): mixed
	 */
	protected $typedValueConstructor;

	/**
	 * A way to construct new empty instance of untyped data during merge.
	 * Corresponding untyped data is passed for convenience.
	 * @var callable(mixed): mixed
	 */
	protected $untypedValueConstructor;

	public function __construct(
		StrategyInterface $valueStrategy,
		callable $typedValueIdentifier,
		callable $untypedValueIdentifier,
		callable $typedValueConstructor,
		callable $untypedValueConstructor
	)
	{
		$this->valueStrategy = $valueStrategy;
		$this->typedValueIdentifier = $typedValueIdentifier;
		$this->untypedValueIdentifier = $untypedValueIdentifier;
		$this->typedValueConstructor = $typedValueConstructor;
		$this->untypedValueConstructor = $untypedValueConstructor;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		return (($from === null) ? null : $this->valueStrategy->extract($from));
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		if ($from !== null)
		{
			if (($to === null) || (($this->untypedValueIdentifier)($from) !== ($this->typedValueIdentifier)($to)))
			{
				$to = ($this->typedValueConstructor)($from);
			}
			$this->valueStrategy->hydrate($from, $to);
		}
		else
		{
			$to = null;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function merge($from, &$to): void
	{
		if ($from !== null)
		{
			if (($to === null) || (($this->untypedValueIdentifier)($from) !== ($this->untypedValueIdentifier)($to)))
			{
				$to = ($this->untypedValueConstructor)($from);
			}
			$this->valueStrategy->merge($from, $to);
		}
		else
		{
			$to = null;
		}
	}
}
