<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class FieldValidatorsWithSubsets
{
	/**
	 * @DTA\Data(subset="subset1")
	 * @DTA\Validator(name="testValidator1", subset="subset1")
	 * @DTA\Data(subset="subset2")
	 * @DTA\Validator(name="testValidator2", subset="subset2")
	 */
	public $testField;
}
