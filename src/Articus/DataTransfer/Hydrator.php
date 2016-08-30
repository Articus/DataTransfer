<?php

namespace Articus\DataTransfer;
use Zend\Hydrator\HydratorInterface;

class Hydrator implements HydratorInterface
{
	/**
	 * @var Metadata
	 */
	protected $metadata;

	/**
	 * @var Strategy\PluginManager
	 */
	protected $strategyPluginManager;

	/**
	 * Internal strategy cache (not every strategy can be cached by plugin manager)
	 * @var Strategy\StrategyInterface[]
	 */
	protected $strategies = [];

	/**
	 * @return Metadata
	 */
	public function getMetadata()
	{
		return $this->metadata;
	}

	/**
	 * @param Metadata $metadata
	 * @return self
	 */
	public function setMetadata(Metadata $metadata = null)
	{
		$this->metadata = $metadata;
		return $this;
	}

	/**
	 * @return Strategy\PluginManager
	 */
	public function getStrategyPluginManager()
	{
		return $this->strategyPluginManager;
	}

	/**
	 * @param Strategy\PluginManager $strategyPluginManager
	 * @return self
	 */
	public function setStrategyPluginManager(Strategy\PluginManager $strategyPluginManager = null)
	{
		$this->strategyPluginManager = $strategyPluginManager;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($object)
	{
		$result = [];
		foreach ($this->metadata->fields as $field)
		{
			$strategy = $this->getStrategy($field);
			$objectValue = $this->invokeGetter($object, $field);
			$result[$field] = ($strategy === null)? $objectValue : $strategy->extract($objectValue, $object);
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate(array $array, $object)
	{
		foreach ($this->metadata->fields as $field)
		{
			if (array_key_exists($field, $array))
			{
				$strategy = $this->getStrategy($field);
				$arrayValue = $array[$field];
				if ($strategy !== null)
				{
					$objectValue = $this->invokeGetter($object, $field);
					$arrayValue = $strategy->hydrate($arrayValue, $objectValue, $array);
				}
				$this->invokeSetter($object, $field, $arrayValue);
			}
		}
		return $object;
	}

	protected function invokeGetter($object, $field)
	{
		switch (true)
		{
			case isset($this->metadata->getters[$field]):
				return $object->{$this->metadata->getters[$field]}();
			case isset($this->metadata->properties[$field]):
				return $object->{$this->metadata->properties[$field]};
//			default:
//				throw new \LogicException(
//					sprintf('Invalid metadata for %s: can not get value of %s.', $this->metadata->className, $field)
//				);
		}
		return null;
	}

	protected function invokeSetter($object, $field, $value)
	{
		switch (true)
		{
			case isset($this->metadata->setters[$field]):
				$object->{$this->metadata->setters[$field]}($value);
				break;
			case isset($this->metadata->properties[$field]):
				$object->{$this->metadata->properties[$field]} = $value;
				break;
//			default:
//				throw new \LogicException(
//					sprintf('Invalid metadata for %s: can not set value of %s.', $this->metadata->className, $field)
//				);
		}
	}

	protected function getStrategy($field)
	{
		$result = null;
		if (isset($this->strategies[$field]))
		{
			$result = $this->strategies[$field];
		}
		elseif (isset($this->metadata->strategies[$field]))
		{
			$declaration = $this->metadata->strategies[$field];
			$result = $this->strategyPluginManager->get($declaration->name, $declaration->options);
			$this->strategies[$field] = $result;
		}
		return $result;
	}
}