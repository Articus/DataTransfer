<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldsWithSameName
{
	/**
	 * @DTA\Data(field="test")
	 */
	public $test1;

	/**
	 * @DTA\Data(field="test")
	 */
	public $test2;
}
