<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

/**
 * @DTA\Validator(name="testValidator1")
 * @DTA\Validator(name="testValidator2", priority=10001)
 */
class ClassFieldWithClassValidator
{
	/**
	 * @DTA\Data()
	 */
	public $test;
}
