<?php
declare(strict_types=1);

namespace spec\Example\DTO;

class Data
{
	public $test1;

	protected $test2;

	public function getTest2()
	{
		return $this->test2;
	}

	public function setTest2($test2): void
	{
		$this->test2 = $test2;
	}
}
