<?php

namespace Test\DataTransfer\Sample;


use Zend\Validator\AbstractValidator;

class ClassValidator extends AbstractValidator
{
	const INVALID = 'invalidClass';

	protected $messageTemplates = [
		self::INVALID => 'Either a or b should not be empty.',
	];

	/**
	 * @inheritDoc
	 */
	public function isValid($value)
	{
		$result = false;
		if (is_array($value))
		{
			$result = (!(empty($value['a']) && empty($value['b'])));
		}
		if (!$result)
		{
			$this->error(self::INVALID);
		}
		return $result;
	}
}