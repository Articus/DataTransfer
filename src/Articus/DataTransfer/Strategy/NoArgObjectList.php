<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

/**
 * Strategy for list of objects that have same specific type which can be constructed without arguments.
 * "List" means something to iterate over but without keys that identify elements - indexed array or Traversable.
 * Null value is allowed.
 */
class NoArgObjectList implements StrategyInterface
{
	/**
	 * @var StrategyInterface
	 */
	protected $typeStrategy;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @param StrategyInterface $typeStrategy
	 * @param string $type
	 */
	public function __construct(StrategyInterface $typeStrategy, string $type)
	{
		$this->typeStrategy = $typeStrategy;
		$this->type = $type;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		$result = null;
		if ($from !== null)
		{
			if (!\is_iterable($from))
			{
				throw new \LogicException(\sprintf(
					'Extraction can be done only from iterable list, not %s',
					\is_object($from) ? \get_class($from) : \gettype($from)
				));
			}
			$result = [];
			foreach ($from as $index => $item)
			{
				if (!($item instanceof $this->type))
				{
					throw new \LogicException(\sprintf(
						'Extraction can be done only from %s, not %s (in item with index "%s")',
						$this->type, \is_object($item) ? \get_class($item) : \gettype($item), $index
					));
				}
				$result[$index] = $this->typeStrategy->extract($item);
			}
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		if ($from !== null)
		{
			if (!\is_iterable($from))
			{
				throw new \LogicException(\sprintf(
					'Hydration can be done only from iterable list, not %s',
					\is_object($from) ? \get_class($from) : \gettype($from)
				));
			}
			$to = [];
			foreach ($from as $index => $item)
			{
				$object = new $this->type();
				$this->typeStrategy->hydrate($item, $object);
				$to[$index] = $object;
			}
		}
	}
}
