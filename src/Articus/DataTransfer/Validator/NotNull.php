<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

/**
 * Simple validator that checks if data is not null.
 * It was added only to remove hard dependency from any specific validation framework.
 */
class NotNull implements ValidatorInterface
{
	const INVALID = 'notNull';

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if ($data === null)
		{
			$result[self::INVALID] = 'Value should not be null.';
		}
		return $result;
	}
}
