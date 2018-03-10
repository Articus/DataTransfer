<?php
namespace Articus\DataTransfer\Metadata\Reader;

use Articus\DataTransfer as DT;
use Doctrine\Common\Annotations\AnnotationReader;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

class Annotation
{
	/**
	 * @var CacheStorage
	 */
	protected $cacheStorage;

	/**
	 * @param CacheStorage $cacheStorage
	 */
	public function __construct(CacheStorage $cacheStorage)
	{
		//TODO check storage capabilities
		$this->cacheStorage = $cacheStorage;
	}

	/**
	 * Returns metadata for specified subset of class fields
	 * @param string $className
	 * @param string $subset
	 * @return DT\Metadata
	 */
	public function getMetadata($className, $subset)
	{
		$classMetadataCacheKey = $this->getClassMetadataCacheKey($className);
		$classMetadata = $this->cacheStorage->getItem($classMetadataCacheKey);
		if ($classMetadata === null)
		{
			$classMetadata = $this->readClassMetadata($className);
			$this->cacheStorage->addItem($classMetadataCacheKey, $classMetadata);
		}
		if (!is_array($classMetadata))
		{
			throw new \LogicException(sprintf('Invalid metadata for class %s.', $className));
		}
		if (!isset($classMetadata[$subset]))
		{
			throw new \LogicException(sprintf('No metadata for subset "%s" in class %s.', $subset, $className));
		}
		$subsetMetadata = $classMetadata[$subset];
		if (!($subsetMetadata instanceof DT\Metadata))
		{
			throw new \LogicException(sprintf('Invalid metadata for subset "%s" in class %s.', $subset, $className));
		}
		return $subsetMetadata;
	}

	/**
	 * Return key for cache adapter to store class metadata
	 * @param string $className
	 * @return string
	 */
	protected function getClassMetadataCacheKey($className)
	{
		return str_replace('\\', '_', $className);
	}

	/**
	 * Reads class metadata from annotations
	 * @param string $className
	 * @return DT\Metadata[]
	 * @throws \ReflectionException
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 */
	protected function readClassMetadata($className)
	{
		/** @var DT\Metadata[] $result */
		$result = [];
		$reflection = new \ReflectionClass($className);
		$reader = new AnnotationReader();
		//Read property annotations
		$propertyFilter = \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE;
		foreach ($reflection->getProperties($propertyFilter) as $property)
		{
			foreach (self::readPropertyAnnotations($property, $reader) as $subset => list($data, $strategy, $validators))
			{
				//Get or create metadata for subset
				$metadata = null;
				if (isset($result[$subset]))
				{
					$metadata = $result[$subset];
				}
				else
				{
					$metadata = new DT\Metadata();
					$metadata->className = $className;
					$metadata->subset = $subset;
					$result[$subset] = $metadata;
				}
				//Process field name
				$field = (empty($data->field)? $property->getName() : $data->field);
				if (in_array($field, $metadata->fields))
				{
					throw new \LogicException(
						sprintf('Invalid metadata for %s: duplicate field %s in subset "%s".', $className, $field, $data->subset)
					);
				}
				$metadata->fields[] = $field;
				//Process direct access property
				if ($property->isPublic())
				{
					$metadata->properties[$field] = $property->getName();
				}
				else
				{
					$methodNameBase = str_replace('_', '', ucwords($property->getName(), '_'));
					if ($data->getter === null)
					{
						$data->getter = 'get'. $methodNameBase;
					}
					if ($data->setter === null)
					{
						$data->setter = 'set'. $methodNameBase;
					}
				}
				//Process property getter
				if ((!empty($data->getter)) && self::isValidGetter($reflection, $data->getter))
				{
					$metadata->getters[$field] = $data->getter;
				}
				//Process property setter
				if ((!empty($data->setter)) && self::isValidSetter($reflection, $data->setter))
				{
					$metadata->setters[$field] = $data->setter;
				}
				//Copy strategy
				$metadata->strategies[$field] = $strategy;
				//Process nullable flag
				$metadata->nullables[$field] = $data->nullable;
				//Copy validators
				$metadata->validators[$field] = $validators;
			}
		}
		//Read class annotations
		foreach ($reader->getClassAnnotations($reflection) as $annotation)
		{
			if (($annotation instanceof DT\Annotation\Validator) && isset($result[$annotation->subset]))
			{
				$result[$annotation->subset]->validators[DT\Validator::GLOBAL_VALIDATOR_KEY][] = $annotation;
			}
		}
		return $result;
	}

	/**
	 * Reads and groups up annotations for specified property
	 * @param \ReflectionProperty $property
	 * @param AnnotationReader $reader
	 * @return \Generator
	 */
	protected static function readPropertyAnnotations(\ReflectionProperty &$property, AnnotationReader &$reader)
	{
		$dataMap = [];
		$strategyMap = [];
		$validatorsMap = [];
		foreach ($reader->getPropertyAnnotations($property) as $annotation)
		{
			switch (true)
			{
				case ($annotation instanceof DT\Annotation\Data):
					$dataMap[$annotation->subset] = $annotation;
					break;
				case ($annotation instanceof DT\Annotation\Strategy):
					$strategyMap[$annotation->subset] = $annotation;
					break;
				case ($annotation instanceof DT\Annotation\Validator):
					$validatorsMap[$annotation->subset][] = $annotation;
					break;
			}
		}
		foreach ($dataMap as $subset => $data)
		{
			$strategy = isset($strategyMap[$subset])? $strategyMap[$subset] : null;
			$validators = isset($validatorsMap[$subset])? $validatorsMap[$subset] : [];
			yield $subset => [$data, $strategy, $validators];
		}
	}

	/**
	 * Validates if class has valid getter method with specified name
	 * @param \ReflectionClass $reflection
	 * @param string $name
	 * @return bool
	 */
	protected static function isValidGetter(\ReflectionClass &$reflection, $name)
	{
		if (!$reflection->hasMethod($name))
		{
			throw new \LogicException(
				sprintf('Invalid metadata for %s: no getter %s.', $reflection->getName(), $name)
			);
		}
		$getter = $reflection->getMethod($name);
		if (!$getter->isPublic())
		{
			throw new \LogicException(
				sprintf('Invalid metadata for %s: getter %s is not public.', $reflection->getName(), $name)
			);
		}
		if ($getter->getNumberOfRequiredParameters() > 0)
		{
			throw new \LogicException(
				sprintf('Invalid metadata for %s: getter %s should not require parameters.', $reflection->getName(), $name)
			);
		}
		return true;
	}

	/**
	 * Validates if class has valid setter method with specified name
	 * @param \ReflectionClass $reflection
	 * @param string $name
	 * @return bool
	 */
	protected static function isValidSetter(\ReflectionClass &$reflection, $name)
	{
		if (!$reflection->hasMethod($name))
		{
			throw new \LogicException(
				sprintf('Invalid metadata for %s: no setter %s.', $reflection->getName(), $name)
			);
		}
		$setter = $reflection->getMethod($name);
		if (!$setter->isPublic())
		{
			throw new \LogicException(
				sprintf('Invalid metadata for %s: setter %s is not public.', $reflection->getName(), $name)
			);
		}
		if ($setter->getNumberOfParameters() < 1)
		{
			throw new \LogicException(
				sprintf('Invalid metadata for %s: setter %s should accept at least one parameter.', $reflection->getName(), $name)
			);
		}
		if ($setter->getNumberOfRequiredParameters() > 1)
		{
			throw new \LogicException(
				sprintf('Invalid metadata for %s: setter %s requires too many parameters.', $reflection->getName(), $name)
			);
		}
		return true;
	}
}