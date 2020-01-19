<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithNonpublicSetter
{
	/**
	 * @DTA\Data(setter="setName")
	 */
	public $test;

	protected function setName($value)
	{
		$this->test = $value;
	}
}
