<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

interface ValidatorInterface
{
	/**
	 * Checks if specified untyped data is valid and returns found violations
	 * @param null|bool|int|float|string|array|\stdClass $data
	 * @return array found violations of validation rules or empty array
	 */
	public function validate($data): array;
}
