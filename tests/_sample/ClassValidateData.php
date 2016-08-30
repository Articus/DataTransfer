<?php
namespace Test\DataTransfer\Sample;

use Articus\DataTransfer\Annotation as DTA;

/**
 * @DTA\Validator(name="ClassValidator")
 */
class ClassValidateData
{
	/**
	 * @DTA\Data(nullable=true)
	 */
	public $a;

	/**
	 * @DTA\Data(nullable=true)
	 */
	public $b;
}