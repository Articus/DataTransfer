<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Annotation;

/**
 * Annotation for declaring data transfer strategy for class or class field
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 */
class Strategy
{
	/**
	 * Name that should be passed to Strategy\PluginManager::get
	 * @Required
	 * @var string
	 */
	public $name;

	/**
	 * Options that should be passed to Strategy\PluginManager::get
	 * @var array | null
	 */
	public $options = null;

	/**
	 * Name of the class metadata subset that annotation belongs to
	 * @var string
	 */
	public $subset = '';
}
