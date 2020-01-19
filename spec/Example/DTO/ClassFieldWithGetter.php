<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithGetter
{
	/**
	 * @DTA\Data(getter="getName")
	 */
	public $test;

	public function getName()
	{
		return $this->test;
	}
}
