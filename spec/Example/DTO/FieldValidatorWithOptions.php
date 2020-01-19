<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class FieldValidatorWithOptions
{
	/**
	 * @DTA\Data()
	 * @DTA\Validator(name="testValidator", options={"test": 123})
	 */
	public $testField;
}
