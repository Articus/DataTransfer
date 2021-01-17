<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class FieldStrategyWithOptions
{
	#[DTA\Data()]
	#[DTA\Strategy(name: "testStrategy", options: ["test" => 123])]
	public $testField;
}
