<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

/**
 * @DTA\Strategy(name="testStrategy")
 */
class ClassFieldWithClassStrategy
{
	/**
	 * @DTA\Data()
	 */
	public $test;
}
