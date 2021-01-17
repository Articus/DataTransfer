<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithName
{
	#[DTA\Data(field: "name")]
	public $test;
}
