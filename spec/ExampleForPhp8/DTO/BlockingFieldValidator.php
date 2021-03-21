<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class BlockingFieldValidator
{
	#[DTA\Data()]
	#[DTA\Validator(name: "testValidator", blocker: true)]
	public $testField;
}
