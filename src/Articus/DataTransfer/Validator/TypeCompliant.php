<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

/**
 * Validator that checks if data is valid according specific type requirements.
 * Null value is allowed.
 */
class TypeCompliant implements ValidatorInterface
{
	protected ValidatorInterface $typeValidator;

	public function __construct(ValidatorInterface $typeValidator)
	{
		$this->typeValidator = $typeValidator;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if ($data !== null)
		{
			$result = $this->typeValidator->validate($data);
		}
		return $result;
	}
}
