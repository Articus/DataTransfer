<?php

namespace Articus\DataTransfer\Annotation;

/**
 * Annotation for validation rule of class value or class field value after extraction
 * @Annotation
 * @Target({"CLASS","PROPERTY","ANNOTATION"})
 */
class Validator
{
	/**
	 * Name that should be passed to PluginManager::get
	 * @Required
	 * @var string
	 */
	public $name;

	/**
	 * Options that should be passed to PluginManager::get
	 * @var array | null
	 */
	public $options = null;

	/**
	 * Priority in which validator should be executed
	 * @var int
	 */
	public $priority = 1;

	/**
	 * Flag if further validation should be skipped when this validator reports violations
	 * @var bool
	 */
	public $blocker = false;

	/**
	 * Name of the class metadata subset that annotation belongs to
	 * @var string
	 */
	public $subset = '';
}
