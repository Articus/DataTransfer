<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class FieldValidatorWithOptions
{
	#[DTA\Data()]
	#[DTA\Validator(name: "testValidator", options: ["test" => 123])]
	public $testField;
}
