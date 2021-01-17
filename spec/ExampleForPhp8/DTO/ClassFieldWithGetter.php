<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithGetter
{
	#[DTA\Data(getter: "getName")]
	public $test;

	public function getName()
	{
		return $this->test;
	}
}
