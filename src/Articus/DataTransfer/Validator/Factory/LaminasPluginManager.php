<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer\Options as DTOptions;
use Articus\DataTransfer\Validator;
use Articus\PluginManager as PM;
use Laminas\Validator\ValidatorPluginManager;
use Psr\Container\ContainerInterface;
use function array_merge_recursive;

class LaminasPluginManager extends PM\Factory\Laminas
{
	public function __construct(string $configKey = DTOptions::DEFAULT_VALIDATOR_PLUGIN_MANAGER)
	{
		parent::__construct($configKey);
	}

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
		if ($container->has(ValidatorPluginManager::class))
		{
			$defaultConfig['abstract_factories'] = [
				Validator\Laminas::class => Laminas::class,
			];
		}

		return array_merge_recursive($defaultConfig, parent::getServiceConfig($container));
	}
}
