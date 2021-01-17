<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithoutSetter
{
	#[DTA\Data(setter: "")]
	public $test;
}
