<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithSetterThatHasNoParameters
{
	/**
	 * @DTA\Data(setter="setName")
	 */
	public $test;

	public function setName()
	{
	}
}
