<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

class FieldStrategy
{
	/**
	 * @DTA\Data()
	 * @DTA\Strategy(name="testStrategy")
	 */
	public $testField;
}
