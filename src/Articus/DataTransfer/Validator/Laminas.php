<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

use Laminas\Validator\ValidatorInterface as LaminasValidator;

/**
 * "Bridge" that allows to check data with any Laminas Validator
 */
class Laminas implements ValidatorInterface
{
	protected LaminasValidator $laminasValidator;

	public function __construct(LaminasValidator $laminasValidator)
	{
		$this->laminasValidator = $laminasValidator;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if (!($data === null || $this->laminasValidator->isValid($data)))
		{
			$result = $this->laminasValidator->getMessages();
		}
		return $result;
	}
}
