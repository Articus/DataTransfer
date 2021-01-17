<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

class ProtectedClassField
{
	#[DTA\Data()]
	protected $test;

	public function getTest()
	{
		return $this->test;
	}

	public function setTest($test): void
	{
		$this->test = $test;
	}
}
