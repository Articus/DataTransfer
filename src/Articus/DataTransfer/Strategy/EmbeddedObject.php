<?php

namespace Articus\DataTransfer\Strategy;
use Articus\DataTransfer\Service;

class EmbeddedObject implements StrategyInterface
{
	/**
	 * @var Service
	 */
	protected $dataTransferService;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $subset;

	/**
	 * @param array $options
	 */
	public function __construct(array $options = null)
	{
		$type = isset($options['type'])? (string) $options['type'] : null;
		if (empty($type) || (!class_exists($type)))
		{
			throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
		}
		$this->type = $type;
		$this->subset = isset($options['subset'])? (string) $options['subset'] : '';
	}


	/**
	 * @return Service
	 */
	public function getDataTransferService()
	{
		return $this->dataTransferService;
	}

	/**
	 * @param Service $dataTransferService
	 * @return self
	 */
	public function setDataTransferService(Service $dataTransferService = null)
	{
		$this->dataTransferService = $dataTransferService;
		return $this;
	}

	public function getHydrator()
	{
		$metadata = $this->dataTransferService->getMetadataReader()->getMetadata($this->type, $this->subset);
		return $this->dataTransferService->getHydrator($metadata);
	}

	/**
	 * @inheritDoc
	 */
	public function extract($objectValue, $object = null)
	{
		$result = null;
		if ($objectValue !== null)
		{
			$result = $this->getHydrator()->extract($objectValue);
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($arrayValue, $objectValue, array $array = null)
	{
		$result = null;
		if ($arrayValue !== null)
		{
			if ($objectValue === null)
			{
				//TODO use Doctrine instantiator instead?
				$objectValue = new $this->type();
			}
			$result = $this->getHydrator()->hydrate($arrayValue, $objectValue);
		}
		return $result;
	}
}