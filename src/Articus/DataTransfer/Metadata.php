<?php
namespace Articus\DataTransfer;

class Metadata
{
	/**
	 * @var string
	 */
	public $className;

	/**
	 * List of all fields that can participate in transferring
	 * @var string[]
	 */
	public $fields = [];

	/**
	 * Field name -> public property name
	 * @var string[]
	 */
	public $properties = [];

	/**
	 * Field name -> getter method name
	 * @var string[]
	 */
	public $getters = [];

	/**
	 * Field name -> getter method name
	 * @var string[]
	 */
	public $setters = [];

	/**
	 * Field name -> annotation
	 * @var Annotation\Strategy[]
	 */
	public $strategies = [];

	/**
	 * Field name -> nullable flag
	 * @var bool[]
	 */
	public $nullables = [];

	/**
	 * Field name -> annotations
	 * @var Annotation\Validator[][]
	 */
	public $validators = [Validator::GLOBAL_VALIDATOR_KEY => []];
}