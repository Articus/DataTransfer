<?php
declare(strict_types=1);

namespace Articus\DataTransfer\PhpAttribute;

/**
 * PHP attribute for declaring data transfer strategy for class or class field
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Strategy
{
	public function __construct(
		/**
		 * Name that should be passed to Strategy\PluginManager::get
		 */
		public string $name,
		/**
		 * Options that should be passed to Strategy\PluginManager::get
		 */
		public null|array $options = null,
		/**
		 * Name of the class metadata subset that annotation belongs to
		 */
		public string $subset = '',
	)
	{
	}
}
