<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithSetterThatHasNoParameters
{
	#[DTA\Data(setter: "setName")]
	public $test;

	public function setName()
	{
	}
}
