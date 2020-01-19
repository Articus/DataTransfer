<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithSetterThatRequiresTwoParameters
{
	/**
	 * @DTA\Data(setter="setName")
	 */
	public $test;

	public function setName($value, $parameter)
	{
		$this->test = $value . $parameter;
	}
}
