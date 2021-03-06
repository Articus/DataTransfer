<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Exception;

interface HydratorInterface
{
	/**
	 * Hydrates source untyped data to destination
	 * @param null|bool|int|float|string|array|\stdClass $from
	 * @param mixed $to
	 * @throws Exception\InvalidData
	 */
	public function hydrate($from, &$to): void;
}
