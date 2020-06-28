<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

/**
 * Strategy for object that have specific type which can be constructed without arguments.
 * Null value is allowed.
 */
class NoArgObject implements StrategyInterface
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
			if (!($from instanceof $this->type))
			{
				throw new \LogicException(\sprintf(
					'Extraction can be done only from %s, not %s',
					$this->type, \is_object($from) ? \get_class($from) : \gettype($from)
				));
			}
			$result = $this->typeStrategy->extract($from);
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
			if ($to === null)
			{
				//TODO use Doctrine instanciator?
				$to = new $this->type();
			}
			if (!($to instanceof $this->type))
			{
				throw new \LogicException(\sprintf(
					'Hydration can be done only to %s, not %s',
					$this->type, \is_object($to) ? \get_class($to) : \gettype($to)
				));
			}
			$this->typeStrategy->hydrate($from, $to);
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
			if ($to === null)
			{
				//TODO use Doctrine instanciator?
				$object = new $this->type();
				$to = $this->typeStrategy->extract($object);
			}
			$this->typeStrategy->merge($from, $to);
		}
		else
		{
			$to = null;
		}
	}
}
