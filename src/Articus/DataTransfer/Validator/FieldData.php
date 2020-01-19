<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

use Articus\DataTransfer\Utility;

/**
 * Validator that checks if data is a map containing specific valid field set.
 * "Map" means something to give value by key - array, stdClass or ArrayAccess.
 * Null value is not allowed.
 */
class FieldData implements ValidatorInterface
{
	const INVALID = 'objectInvalid';
	const INVALID_INNER = 'objectInvalidInner';

	/**
	 * @psalm-var iterable<array{0: string, 1: ValidatorInterface}>
	 */
	protected $fields;

	/**
	 * @param iterable $fields list of tuples (<field name>, <field value validator>)
	 */
	public function __construct(iterable $fields)
	{
		$this->fields = $fields;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		$map = new Utility\MapAccessor($data);
		if ($map->accessible())
		{
			foreach ($this->fields as [$fieldName, $validator])
			{
				/** @var ValidatorInterface $validator */
				$fieldViolations = $validator->validate($map->get($fieldName));
				if (!empty($fieldViolations))
				{
					$result[self::INVALID_INNER][$fieldName] = $fieldViolations;
				}
			}
		}
		else
		{
			$result[self::INVALID] = \sprintf(
				'Invalid data: expecting key-value map, not %s.', \is_object($data) ? \get_class($data) : \gettype($data)
			);
		}
		return $result;
	}
}
