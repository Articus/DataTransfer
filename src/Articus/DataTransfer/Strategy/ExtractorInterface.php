<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Exception;

interface ExtractorInterface
{
	/**
	 * Extracts untyped data from source
	 * @param mixed $from
	 * @return null|bool|int|float|string|array|\stdClass
	 * @throws Exception\InvalidData
	 */
	public function extract($from);
}
