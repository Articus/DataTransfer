<?php
namespace Test\DataTransfer\Sample;

use Articus\DataTransfer\Annotation as DTA;

class ValidateData
{
	/**
	 * @DTA\Data()
	 * @DTA\Validator(name="Hex")
	 * @DTA\Validator(name="StringLength",options={"min": 1, "max": 5}, priority=2)
	 */
	public $scalar;

	/**
	 * @DTA\Data(nullable=true)
	 * @DTA\Validator(name="Hex")
	 * @DTA\Validator(name="StringLength",options={"min": 1, "max": 5}, priority=2)
	 */
	public $nullableScalar;

	/**
	 * @DTA\Data()
	 * @DTA\Validator(name="Collection",options={"validators":{
	 *     @DTA\Validator(name="StringLength",options={"min": 1, "max": 5}),
	 *     @DTA\Validator(name="Hex"),
	 * }})
	 */
	public $scalarArray;

	/**
	 * @DTA\Data(nullable=true)
	 * @DTA\Validator(name="Collection",options={"validators":{
	 *     @DTA\Validator(name="StringLength",options={"min": 1, "max": 5}),
	 *     @DTA\Validator(name="Hex"),
	 * }})
	 */
	public $nullableScalarArray;

	/**
	 * @DTA\Data()
	 * @DTA\Strategy(name="Object", options={"type":EmbeddedData::class})
	 * @DTA\Validator(name="Dictionary", options={"type":EmbeddedData::class})
	 */
	public $object;

	/**
	 * @DTA\Data(nullable=true)
	 * @DTA\Strategy(name="Object", options={"type":EmbeddedData::class})
	 * @DTA\Validator(name="Dictionary", options={"type":EmbeddedData::class})
	 */
	public $nullableObject;

	/**
	 * @DTA\Data()
	 * @DTA\Strategy(name="ObjectArray", options={"type":EmbeddedData::class})
	 * @DTA\Validator(name="Collection",options={"validators":{
	 *     {"name":"Dictionary", "options":{"type":EmbeddedData::class}}
	 * }})
	 */
	public $objectArray;

	/**
	 * @DTA\Data(nullable=true)
	 * @DTA\Strategy(name="ObjectArray", options={"type":EmbeddedData::class})
	 * @DTA\Validator(name="Collection",options={"validators":{
	 *     {"name": "Dictionary", "options": {"type":EmbeddedData::class}}
	 * }})
	 */
	public $nullableObjectArray;
}