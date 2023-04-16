<?php
declare(strict_types=1);

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
	 */
	public string $name;

	/**
	 * Options that should be passed to PluginManager::get
	 */
	public array $options = [];

	/**
	 * Priority in which validator should be executed
	 */
	public int $priority = 1;

	/**
	 * Flag if further validation should be skipped when this validator reports violations
	 */
	public bool $blocker = false;

	/**
	 * Name of the class metadata subset that annotation belongs to
	 */
	public string $subset = '';
}
