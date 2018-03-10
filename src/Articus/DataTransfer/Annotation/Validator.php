<?php

namespace Articus\DataTransfer\Annotation;

/**
 * Annotation for validation rule of field value after extraction
 * @Annotation
 * @Target({"CLASS","PROPERTY","ANNOTATION"})
 */
class Validator
{
	/**
	 * Name that should be passed to PluginManager::get
	 * @var string
	 */
	public $name;

	/**
	 * Options that should be passed to PluginManager::get
	 * @var array
	 */
	public $options = null;

	/**
	 * Priority in which validator should be performed
	 * @var int
	 */
	public $priority = 1;

	/**
	 * Name of the object field subset with additional separate metadata that this annotation belongs to
	 * @var string
	 */
	public $subset = '';
}