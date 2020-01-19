<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithoutSetter
{
	/**
	 * @DTA\Data(setter="")
	 */
	public $test;
}
