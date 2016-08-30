<?php

namespace Articus\DataTransfer\Annotation;

/**
 * Annotation for declaring object field that should be hydrated and extracted
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Data
{
	/**
	 * Name of the field in array that will be used in hydration/extraction. 
	 * If empty, property name is used
	 * @var string
	 */
	public $field = null;

	/**
	 * Name of the method that allow to get property value.
	 * If null and property is public, direct access is used.
	 * If null and property is not public, ("get" . <property name in camel case>) is used.
	 * @var string
	 */
	public $getter = null;

	/**
	 * Name of the method that allow to set property value.
	 * If null and property is public, direct access is used.
	 * If null and property is not public, ("set" . <property name in camel case>) is used.
	 * @var string
	 */
	public $setter = null;

	/**
	 * Flag if field value in array is allowed to be null
	 * @var bool
	 */
	public $nullable = false;
}