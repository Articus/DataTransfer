<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithSubsets
{
	/**
	 * @DTA\Data(field="test1", subset="subset1")
	 * @DTA\Data(field="test2", subset="subset2")
	 */
	public $test;
}
