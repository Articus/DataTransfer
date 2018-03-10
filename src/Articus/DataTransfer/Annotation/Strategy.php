<?php

namespace Articus\DataTransfer\Annotation;

/**
 * Annotation for declaring hydrator strategy for field
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Strategy
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
	 * Name of the object field subset with additional separate metadata that this annotation belongs to
	 * @var string
	 */
	public $subset = null;
}