<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class FieldStrategiesWithSameSubsetForSameProperty
{
	#[DTA\Data()]
	#[DTA\Strategy(name: "testStrategy1")]
	#[DTA\Strategy(name: "testStrategy2")]
	public $test;
}
