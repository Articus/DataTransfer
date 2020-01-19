<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

/**
 * Dummy strategy that does not modify data
 */
class Whatever implements StrategyInterface
{
	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		return $from;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		$to = $from;
	}
}
