<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldsWithSameName
{
	#[DTA\Data(field: "test")]
	public $test1;

	#[DTA\Data(field: "test")]
	public $test2;
}
