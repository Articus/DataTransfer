<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class PrivateClassField
{
	/**
	 * @DTA\Data()
	 */
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
