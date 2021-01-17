<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class FieldStrategiesWithSubsets
{
	#[DTA\Data(subset: "subset1")]
	#[DTA\Strategy(name: "testStrategy1", subset: "subset1")]
	#[DTA\Data(subset: "subset2")]
	#[DTA\Strategy(name: "testStrategy2", subset: "subset2")]
	public $testField;
}
