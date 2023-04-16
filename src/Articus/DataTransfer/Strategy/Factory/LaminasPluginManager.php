<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer\Strategy;
use Articus\PluginManager as PM;
use Psr\Container\ContainerInterface;
use function array_merge_recursive;

class LaminasPluginManager extends PM\Factory\Laminas
{
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
