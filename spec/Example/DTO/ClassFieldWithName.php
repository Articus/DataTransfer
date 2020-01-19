<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithName
{
	/**
	 * @DTA\Data(field="name")
	 */
	public $test;
}
