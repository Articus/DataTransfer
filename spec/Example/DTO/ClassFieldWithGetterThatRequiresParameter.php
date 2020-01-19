<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class ClassFieldWithGetterThatRequiresParameter
{
	/**
	 * @DTA\Data(getter="getName")
	 */
	public $test;

	public function getName($parameter)
	{
		return $this->test . $parameter;
	}
}
