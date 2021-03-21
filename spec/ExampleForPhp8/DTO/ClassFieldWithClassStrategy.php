<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

#[DTA\Strategy(name: "testStrategy")]
class ClassFieldWithClassStrategy
{
	#[DTA\Data()]
	public $test;
}
