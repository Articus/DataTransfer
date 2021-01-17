<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class PublicClassField
{
	#[DTA\Data()]
	public $test;
}
