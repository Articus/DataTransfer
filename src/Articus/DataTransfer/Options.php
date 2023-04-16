<?php
declare(strict_types=1);

namespace Articus\DataTransfer;

class Options
{
	const DEFAULT_STRATEGY_PLUGIN_MANAGER = 'Articus\DataTransfer\Strategy\PluginManager';
	const DEFAULT_VALIDATOR_PLUGIN_MANAGER = 'Articus\DataTransfer\Validator\PluginManager';

	/**
	 * Container service name of metadata provider that should be used
	 * @var string
	 */
	public string $metadataProvider = ClassMetadataProviderInterface::class;

	/**
	 * Container service name of strategy plugin manager that should be used
	 * @var string
	 */
	public string $strategyPluginManager = self::DEFAULT_STRATEGY_PLUGIN_MANAGER;

	/**
	 * Container service name of validator plugin manager that should be used
	 * @var string
	 */
	public string $validatorPluginManager = self::DEFAULT_VALIDATOR_PLUGIN_MANAGER;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'metadata_provider':
				case 'metadataProvider':
					$this->metadataProvider = $value;
					break;
				case 'strategy_manager':
				case 'strategyManager':
				case 'strategy_plugin_manager':
				case 'strategyPluginManager':
					$this->strategyPluginManager = $value;
					break;
				case 'validator_manager':
				case 'validatorManager':
				case 'validator_plugin_manager':
				case 'validatorPluginManager':
					$this->validatorPluginManager = $value;
					break;
			}
		}
	}
}
