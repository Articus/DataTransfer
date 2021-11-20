<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;

class PluginManager extends AbstractPluginManager
{
	public const S_OBJECT = 'Object';
	public const S_OBJECT_ARRAY = 'ObjectArray';

	protected $instanceOf = StrategyInterface::class;

	protected $factories = [
		FieldData::class => Factory\FieldData::class,
		Identifier::class => Factory\Identifier::class,
		self::S_OBJECT => Factory\NoArgObject::class,
		self::S_OBJECT_ARRAY => Factory\NoArgObjectList::class,
		Whatever::class => InvokableFactory::class,
	];

	protected $aliases = [
		'object' => self::S_OBJECT,
		'objectArray' => self::S_OBJECT_ARRAY,
	];

	protected $shared = [
		Whatever::class => true,
	];

	/**
	 * Overwrite parent method just to add return type declaration
	 * @inheritDoc
	 * @return StrategyInterface
	 */
	public function get($name, array $options = null): StrategyInterface
	{
		return parent::get($name, $options);
	}
}
