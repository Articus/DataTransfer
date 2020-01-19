<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

/**
 * Composite validator that executes other validators sequentially and returns all found violations combined together.
 * Each chain "link" maybe be marked as blocker - to stop validation early and prevent execution of the following "links".
 */
class Chain implements ValidatorInterface
{
	/**
	 * @psalm-var array<array{0: ValidatorInterface, 1: bool}>
	 */
	protected $links = [];

	/**
	 * @param iterable $links list of tuples (<validator>, <flag if validator is blocker>)
	 */
	public function __construct(iterable $links)
	{
		$this->links = $links;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];

		foreach ($this->links as [$validator, $blocker])
		{
			/** @var ValidatorInterface $validator */
			$violations = $validator->validate($data);
			$result = \array_merge_recursive($result, $violations);
			if ($blocker && (!empty($violations)))
			{
				break;
			}
		}
		return $result;
	}
}
