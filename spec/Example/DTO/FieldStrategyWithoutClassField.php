<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class FieldStrategyWithoutClassField
{
	/**
	 * @DTA\Strategy(name="testStrategy")
	 */
	public $testField;
}
