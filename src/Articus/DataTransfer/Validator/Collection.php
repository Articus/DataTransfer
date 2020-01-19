<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

/**
 * Validator that checks if data is a collection of valid items.
 * "Collection" means something to iterate over - array, stdClass or Traversable.
 * Null value is allowed.
 */
class Collection implements ValidatorInterface
{
	public const INVALID = 'collectionInvalid';
	public const INVALID_INNER = 'collectionInvalidInner';

	/**
	 * @var ValidatorInterface
	 */
	protected $itemValidator;

	/**
	 * @param ValidatorInterface $itemValidator
	 */
	public function __construct(ValidatorInterface $itemValidator)
	{
		$this->itemValidator = $itemValidator;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if ($data !== null)
		{
			if (\is_iterable($data) || ($data instanceof \stdClass))
			{
				foreach ($data as $key => $item)
				{
					$itemViolations = $this->itemValidator->validate($item);
					if (!empty($itemViolations))
					{
						$result[self::INVALID_INNER][$key] = $itemViolations;
					}
				}
			}
			else
			{
				$result[self::INVALID] = \sprintf(
					'Invalid data: expecting iterable collection, not %s.', \is_object($data) ? \get_class($data) : \gettype($data)
				);
			}
		}
		return $result;
	}
}
