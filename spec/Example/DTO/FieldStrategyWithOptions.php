<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class FieldStrategyWithOptions
{
	/**
	 * @DTA\Data()
	 * @DTA\Strategy(name="testStrategy", options={"test": 123})
	 */
	public $testField;
}
