<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\IdentifiableValueLoader;
use function is_int;
use function is_string;

/**
 * Strategy for immutable values that can be fully represented by their identifiers.
 */
class Identifier implements StrategyInterface
{
	protected IdentifiableValueLoader $loader;

	protected string $type;

	public function __construct(IdentifiableValueLoader $loader, string $type)
	{
		$this->loader = $loader;
		$this->type = $type;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		$result = ($from === null) ? null : $this->loader->identify($this->type, $from);
		if ($result !== null)
		{
			$this->loader->bank($this->type, $from);
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
			$toId = ($to === null) ? null : $this->loader->identify($this->type, $to);
			if ($from !== $toId)
			{
				$to = $this->loader->get($this->type, $from);
			}
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
		if (is_int($from) || is_string($from))
		{
			$this->loader->wish($this->type, $from);
		}
		$to = $from;
	}
}
