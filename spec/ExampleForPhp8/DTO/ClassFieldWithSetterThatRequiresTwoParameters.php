<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithSetterThatRequiresTwoParameters
{
	#[DTA\Data(setter: "setName")]
	public $test;

	public function setName($value, $parameter)
	{
		$this->test = $value . $parameter;
	}
}
