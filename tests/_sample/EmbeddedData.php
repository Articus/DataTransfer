<?php
namespace Test\DataTransfer\Sample;

use Articus\DataTransfer\Annotation as DTA;

class EmbeddedData
{
	/**
	 * @DTA\Data()
	 * @DTA\Validator(name="StringLength",options={"min": 1, "max": 5})
	 * @DTA\Validator(name="Hex")
	 */
	public $property;

	/**
	 * @DTA\Data(nullable=true)
	 * @DTA\Validator(name="StringLength",options={"min": 1, "max": 5})
	 * @DTA\Validator(name="Hex")
	 */
	public $nullableProperty;
}