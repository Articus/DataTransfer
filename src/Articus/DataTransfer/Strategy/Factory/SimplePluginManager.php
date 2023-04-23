<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\Options as DTOptions;
use Articus\DataTransfer\Strategy;
use Articus\PluginManager as PM;
use Psr\Container\ContainerInterface;
use function array_merge_recursive;

class SimplePluginManager extends PM\Factory\Simple
{
	public function __construct(string $configKey = DTOptions::DEFAULT_STRATEGY_PLUGIN_MANAGER)
	{
		parent::__construct($configKey);
	}

	protected function getServiceConfig(ContainerInterface $container): array
	{
		$defaultConfig = [
			'invokables' => [
				Strategy\Whatever::class => Strategy\Whatever::class,
			],
			'factories' => [
				Strategy\FieldData::class => FieldData::class,
				Strategy\Identifier::class => Identifier::class,
				'Object' => NoArgObject::class,
				'ObjectArray' => NoArgObjectList::class,
			],
			'aliases' => [
				'object' => 'Object',
				'objectArray' => 'ObjectArray',
			],
		];

		return array_merge_recursive($defaultConfig, parent::getServiceConfig($container));
	}
}
