<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Annotation;

/**
 * Annotation for declaring class field that should be hydrated and extracted
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Data
{
	/**
	 * Name of the field in array that will be used in hydration/extraction. 
	 * If null, property name is used.
	 */
	public ?string $field = null;

	/**
	 * Name of the method that allows to get property value.
	 * If null and property is public, direct access is used.
	 * If null and property is not public, ("get" . <property name in camel case>) is used.
	 * If empty string, there is no way to get property value.
	 */
	public ?string $getter = null;

	/**
	 * Name of the method that allows to set property value.
	 * If null and property is public, direct access is used.
	 * If null and property is not public, ("set" . <property name in camel case>) is used.
	 * If empty string, there is no way to set property value.
	 */
	public ?string $setter = null;

	/**
	 * Flag if field value in array is allowed to be null
	 */
	public bool $nullable = false;

	/**
	 * Name of the class metadata subset that annotation belongs to
	 */
	public string $subset = '';
}
