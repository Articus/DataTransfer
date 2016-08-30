<?php

namespace Articus\DataTransfer\Strategy;
use Zend\ServiceManager\AbstractPluginManager;

class PluginManager extends AbstractPluginManager
{
	/**
	 * @inheritdoc
	 */
	protected $instanceOf = StrategyInterface::class;

	/**
	 * @inheritdoc
	 */
	protected $factories = [
		EmbeddedObject::class => Factory::class,
		EmbeddedObjectArray::class => Factory::class,
	];

	/**
	 * @inheritdoc
	 */
	protected $aliases = [
		'Object' => EmbeddedObject::class,
		'object' => EmbeddedObject::class,
		'ObjectArray' => EmbeddedObjectArray::class,
		'objectArray' => EmbeddedObjectArray::class,
	];

	/**
	 * Just for correct auto complete
	 * @inheritdoc
	 * @return StrategyInterface
	 */
	public function get($name, array $options = null)
	{
		return parent::get($name, $options);
	}
}