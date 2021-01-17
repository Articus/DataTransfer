<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ClassFieldWithNonpublicGetter
{
	#[DTA\Data(getter: "getName")]
	public $test;

	protected function getName()
	{
		return $this->test;
	}
}
