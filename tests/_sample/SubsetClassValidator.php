<?php

namespace Test\DataTransfer\Sample;

use Zend\Validator\AbstractValidator;

class SubsetClassValidator extends AbstractValidator
{
	const INVALID = 'invalidSubsetClass';

	protected $messageTemplates = [
		self::INVALID => 'Properties "ab" and "%prop%" should equal.',
	];

	protected $messageVariables = [
		'prop' => 'prop'
	];

	/**
	 * @var string
	 */
	protected $prop;

	public function __construct(array $options)
	{
		$this->prop = isset($options['prop'])? $options['prop'] : '';
		parent::__construct();
	}


	/**
	 * @inheritDoc
	 */
	public function isValid($value)
	{
		$result = false;
		if (is_array($value))
		{
			$result = isset($value['ab'], $value[$this->prop]) && ($value['ab'] === $value[$this->prop]);
		}
		if (!$result)
		{
			$this->error(self::INVALID);
		}
		return $result;
	}
}