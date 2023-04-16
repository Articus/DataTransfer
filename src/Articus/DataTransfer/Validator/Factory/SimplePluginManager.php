<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\Validator;
use Articus\PluginManager as PM;
use Psr\Container\ContainerInterface;
use function array_merge_recursive;

class SimplePluginManager extends PM\Factory\Simple
{
	protected function getServiceConfig(ContainerInterface $container): array
	{
		$defaultConfig = [
			'invokables' => [
				Validator\NotNull::class => Validator\NotNull::class,
				Validator\Whatever::class => Validator\Whatever::class,
			],
			'factories' => [
				Validator\Chain::class => Chain::class,
				Validator\Collection::class => Collection::class,
				Validator\FieldData::class => FieldData::class,
				Validator\Identifier::class => Identifier::class,
				Validator\TypeCompliant::class => TypeCompliant::class,
			],
			'aliases' => [
				'Collection' => Validator\Collection::class,
				'collection' => Validator\Collection::class,
				'TypeCompliant' => Validator\TypeCompliant::class,
				'typeCompliant' => Validator\TypeCompliant::class,
			],
		];

		return array_merge_recursive($defaultConfig, parent::getServiceConfig($container));
	}
}
