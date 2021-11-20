<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;

class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = ValidatorInterface::class;

	protected $factories = [
		Chain::class => Factory\Chain::class,
		Collection::class => Factory\Collection::class,
		FieldData::class => Factory\FieldData::class,
		Identifier::class => Factory\Identifier::class,
		NotNull::class => InvokableFactory::class,
		TypeCompliant::class => Factory\TypeCompliant::class,
		Whatever::class => InvokableFactory::class,
	];

	protected $aliases = [
		'Collection' => Collection::class,
		'collection' => Collection::class,
		'TypeCompliant' => TypeCompliant::class,
		'typeCompliant' => TypeCompliant::class,
	];

	protected $shared = [
		NotNull::class => true,
		Whatever::class => true,
	];

	/**
	 * Overwrite parent method just to add return type declaration
	 * @inheritDoc
	 * @return ValidatorInterface
	 */
	public function get($name, array $options = null): ValidatorInterface
	{
		return parent::get($name, $options);
	}
}
