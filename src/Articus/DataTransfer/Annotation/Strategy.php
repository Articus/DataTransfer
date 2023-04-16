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
	 */
	public string $name;

	/**
	 * Options that should be passed to Strategy\PluginManager::get
	 */
	public array $options = [];

	/**
	 * Name of the class metadata subset that annotation belongs to
	 */
	public string $subset = '';
}
