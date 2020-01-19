<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class FieldStrategiesWithSameSubsetForSameProperty
{
	/**
	 * @DTA\Data()
	 * @DTA\Strategy(name="testStrategy1")
	 * @DTA\Strategy(name="testStrategy2")
	 */
	public $test;
}
