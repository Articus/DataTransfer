<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Exception;

interface HydratorInterface
{
	/**
	 * Merges source data to destination data
	 * @param mixed $from
	 * @param mixed $to
	 * @throws Exception\InvalidData
	 */
	public function hydrate($from, &$to): void;
}
