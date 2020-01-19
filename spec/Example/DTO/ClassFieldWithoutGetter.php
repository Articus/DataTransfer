<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithoutGetter
{
	/**
	 * @DTA\Data(getter="")
	 */
	public $test;
}
