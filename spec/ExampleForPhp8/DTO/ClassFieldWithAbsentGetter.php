<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithAbsentGetter
{
		#[DTA\Data(getter: "getName")]
		public $test;
}
