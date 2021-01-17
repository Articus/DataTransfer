<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class FieldValidatorsWithSameSubset
{
	#[DTA\Data()]
	#[DTA\Validator(name: "testValidator1")]
	#[DTA\Validator(name: "testValidator2", priority: 0)]
	#[DTA\Validator(name: "testValidator3", priority: 2)]
	#[DTA\Validator(name: "testValidator4", priority: 1)]
	#[DTA\Validator(name: "testValidator5", priority: 2)]
	#[DTA\Validator(name: "testValidator6", priority: 3)]
	#[DTA\Validator(name: "testValidator7", priority: 10001)]
	public $testField;
}
