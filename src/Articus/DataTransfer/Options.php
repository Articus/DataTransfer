<?php
namespace Articus\DataTransfer;

use Zend\Stdlib\AbstractOptions;

class Options extends AbstractOptions
{
	/**
	 * Configuration for cache storage
	 * @var array|string
	 */
	protected $metadataCache = [];

	/**
	 * Configuration for strategy plugin manager
	 * @var array|string
	 */
	protected $strategies = [];

	/**
	 * Configuration for validator plugin manager
	 * @var array|string
	 */
	protected $validators = [];

	/**
	 * @return array|string
	 */
	public function getMetadataCache()
	{
		return $this->metadataCache;
	}

	/**
	 * @param array|string $metadataCache
	 * @return self
	 */
	public function setMetadataCache($metadataCache)
	{
		$this->metadataCache = $metadataCache;
		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getStrategies()
	{
		return $this->strategies;
	}

	/**
	 * @param array|string $strategies
	 * @return self
	 */
	public function setStrategies($strategies)
	{
		$this->strategies = $strategies;
		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getValidators()
	{
		return $this->validators;
	}

	/**
	 * @param array|string $validators
	 * @return self
	 */
	public function setValidators($validators)
	{
		$this->validators = $validators;
		return $this;
	}
}