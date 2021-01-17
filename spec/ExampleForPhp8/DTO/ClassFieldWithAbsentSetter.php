<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithAbsentSetter
{
		#[DTA\Data(setter: "setName")]
		public $test;
}
