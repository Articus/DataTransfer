<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

use function is_string;

/**
 * Configurable validator that checks if data is a valid serialized value.
 */
class SerializableValue implements ValidatorInterface
{
	public const INVALID = 'serializedValueInvalid';
	public const INVALID_INNER = 'serializedValueInvalidInner';

	/**
	 * Internal validator for unserialized values
	 */
	protected ValidatorInterface $valueValidator;

	/**
	 * A way to decode value from string
	 * @var callable(string): mixed
	 */
	protected $unserializer;

	/**
	 * @param ValidatorInterface $valueValidator
	 * @param callable $unserializer
	 */
	public function __construct(ValidatorInterface $valueValidator, callable $unserializer)
	{
		$this->valueValidator = $valueValidator;
		$this->unserializer = $unserializer;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if ($data !== null)
		{
			if (is_string($data))
			{
				$value = ($this->unserializer)($data);
				$valueViolations = $this->valueValidator->validate($value);
				if (!empty($valueViolations))
				{
					$result[self::INVALID_INNER] = $valueViolations;
				}
			}
			else
			{
				$result[self::INVALID] = 'Invalid data: expecting string.';
			}
		}
		return $result;
	}
}
