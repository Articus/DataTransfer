<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Exception;

interface MergerInterface
{
	/**
	 * Merges source untyped data into destination untyped data
	 * @param null|bool|int|float|string|array|\stdClass $from
	 * @param null|bool|int|float|string|array|\stdClass $to
	 * @throws Exception\InvalidData
	 */
	public function merge($from, &$to): void;
}
