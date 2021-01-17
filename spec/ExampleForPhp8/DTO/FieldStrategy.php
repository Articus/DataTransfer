<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class FieldStrategy
{
	#[DTA\Data()]
	#[DTA\Strategy(name: "testStrategy")]
	public $testField;
}
