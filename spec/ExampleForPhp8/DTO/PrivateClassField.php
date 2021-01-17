<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class PrivateClassField
{
	#[DTA\Data()]
	private $test;

	public function getTest()
	{
		return $this->test;
	}

	public function setTest($test): void
	{
		$this->test = $test;
	}
}
