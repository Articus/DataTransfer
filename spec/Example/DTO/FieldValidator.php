<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class FieldValidator
{
	/**
	 * @DTA\Data()
	 * @DTA\Validator(name="testValidator")
	 */
	public $testField;
}
