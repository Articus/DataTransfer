<?php
namespace Test\DataTransfer\Sample;

use Articus\DataTransfer\Strategy\StrategyInterface;

class PrefixStrategy implements StrategyInterface
{
	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * PrefixStrategy constructor.
	 */
	public function __construct(array $options)
	{
		$this->prefix = isset($options['prefix'])? $options['prefix'] : 'empty';
	}

	/**
	 * @inheritDoc
	 */
	public function extract($objectValue, $object = null)
	{
		return sprintf('%s-%d', $this->prefix, $objectValue);
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($arrayValue, $objectValue, array $array = null)
	{
		list($result) = sscanf($arrayValue, sprintf('%s-', $this->prefix).'%i');
		return $result;
	}
}