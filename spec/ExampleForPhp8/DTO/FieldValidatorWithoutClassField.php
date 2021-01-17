<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class FieldValidatorWithoutClassField
{
	#[DTA\Validator(name: "testValidator")]
	public $testField;
}
