<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

/**
 * Dummy validator that treats any data as valid.
 */
class Whatever implements ValidatorInterface
{
	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		return [];
	}
}
