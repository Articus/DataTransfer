<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithNonpublicSetter
{
	#[DTA\Data(setter: "setName")]
	public $test;

	protected function setName($value)
	{
		$this->test = $value;
	}
}
