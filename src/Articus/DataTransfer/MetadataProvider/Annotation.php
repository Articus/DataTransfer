<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataProvider;

use Articus\DataTransfer\Annotation as DTA;
use Articus\DataTransfer\ClassMetadataProviderInterface;
use Articus\DataTransfer\FieldMetadataProviderInterface;
use Articus\DataTransfer\Strategy;
use Articus\DataTransfer\Validator;
use Doctrine\Common\Annotations\AnnotationReader;
use Laminas\Stdlib\FastPriorityQueue;
use Psr\SimpleCache\CacheInterface;

/**
 * Provider that retrieves metadata from class annotations
 */
class Annotation implements ClassMetadataProviderInterface, FieldMetadataProviderInterface
{
	const MAX_VALIDATOR_PRIORITY = 10000;

	/**
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * @psalm-var array<string, array<string, array{0: string, 1: array}>>
	 */
	protected $classStrategies = [];

	/**
	 * @psalm-var array<string, array<string, array{0: string, 1: array}>>
	 */
	protected $classValidators = [];

	/**
	 * @psalm-var array<string, array<string, array<string, array{0: string, 1: null|array{0: string, 1: bool}, 2: null|array{0: string, 1: bool}}>>>
	 */
	protected $classFields = [];

	/**
	 * @psalm-var array<string, array<string, array<string, array{0: string, 1: array}>>>
	 */
	protected $fieldStrategies = [];

	/**
	 * @psalm-var array<string, array<string, array<string, array{0: string, 1: array}>>>
	 */
	protected $fieldValidators = [];

	public function __construct(CacheInterface $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * @inheritDoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getClassStrategy(string $className, string $subset): array
	{
		$this->ascertainMetadata($className);
		$result = $this->classStrategies[$className][$subset] ?? null;
		if ($result === null)
		{
			throw new \LogicException(\sprintf('No strategy for metadata subset "%s" of class %s', $subset, $className));
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getClassValidator(string $className, string $subset): array
	{
		$this->ascertainMetadata($className);
		$result = $this->classValidators[$className][$subset] ?? null;
		if ($result === null)
		{
			throw new \LogicException(\sprintf('No validator for metadata subset "%s" of class %s', $subset, $className));
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getClassFields(string $className, string $subset): iterable
	{
		$this->ascertainMetadata($className);
		$fields = $this->classFields[$className][$subset] ?? null;
		if ($fields === null)
		{
			throw new \LogicException(\sprintf('No fields for metadata subset "%s" of class %s', $subset, $className));
		}
		yield from $fields;
	}

	/**
	 * @inheritDoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getFieldStrategy(string $className, string $subset, string $fieldName): array
	{
		$this->ascertainMetadata($className);
		$result = $this->fieldStrategies[$className][$subset][$fieldName] ?? null;
		if ($result === null)
		{
			throw new \LogicException(\sprintf('No strategy for field %s in metadata subset "%s" of class %s', $fieldName, $subset, $className));
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getFieldValidator(string $className, string $subset, string $fieldName): array
	{
		$this->ascertainMetadata($className);
		$result = $this->fieldValidators[$className][$subset][$fieldName] ?? null;
		if ($result === null)
		{
			throw new \LogicException(\sprintf('No validator for field %s in metadata subset "%s" of class %s', $fieldName, $subset, $className));
		}
		return $result;
	}

	/**
	 * Ascertains that metadata for specified class was loaded
	 * @param string $className
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	protected function ascertainMetadata(string $className): void
	{
		if (empty($this->classStrategies[$className]))
		{
			$metadata = $this->cache->get($className);
			if (empty($metadata))
			{
				$metadata = $this->loadMetadata($className);
				$this->cache->set($className, $metadata);
			}
			[
				$this->classStrategies[$className],
				$this->classValidators[$className],
				$this->classFields[$className],
				$this->fieldStrategies[$className],
				$this->fieldValidators[$className],
			] = $metadata;
		}
	}

	/**
	 * Reads metadata for specified class from its annotations
	 * @param string $className
	 * @return array
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	protected function loadMetadata(string $className): array
	{
		$classStrategies = [];
		$classValidators = [];
		$classFields = [];
		$fieldStrategies = [];
		$fieldValidators = [];

		$classReflection = new \ReflectionClass($className);
		$reader = new AnnotationReader();
		//Read property annotations
		$propertyFilter = \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE;
		foreach ($classReflection->getProperties($propertyFilter) as $propertyReflection)
		{
			foreach ($this->processPropertyAnnotations($classReflection, $propertyReflection, $reader->getPropertyAnnotations($propertyReflection)) as [$subset, $field, $strategy, $validator])
			{
				$fieldName = $field[0];
				if (!empty($classFields[$subset][$fieldName]))
				{
					throw new \LogicException(\sprintf('Duplicate field "%s" declaration for subset %s of class %s', $fieldName, $subset, $className));
				}
				$classFields[$subset][$fieldName] = $field;
				$fieldStrategies[$subset][$fieldName] = $strategy;
				$fieldValidators[$subset][$fieldName] = $validator;
			}
		}
		//Read class annotations
		$propertySubsets = \array_keys($classFields);
		foreach ($this->processClassAnnotations($className, $reader->getClassAnnotations($classReflection), $propertySubsets) as [$subset, $strategy, $validator])
		{
			$classStrategies[$subset] = $strategy;
			$classValidators[$subset] = $validator;
		}

		return [$classStrategies, $classValidators, $classFields, $fieldStrategies, $fieldValidators];
	}

	/**
	 * @param string $className
	 * @param iterable $annotations
	 * @param iterable $propertySubsetNames
	 * @return \Generator tuples (<subset>, <strategy declaration>, <validator declaration>)
	 * @psalm-return \Generator<array{0: string, 1: array{0: string, 1: null|array}, 2: array{0: string, 1: array}}>
	 */
	protected function processClassAnnotations(string $className, iterable $annotations, iterable $propertySubsetNames): \Generator
	{
		/** @psalm-var array<string, array{0: array{0: string, 1: null|array}, 1: FastPriorityQueue<array{0: string, 1: array, 2: bool}>}> $subsets */
		/** @var array|array[][]|FastPriorityQueue[][] $subsets */
		$subsets = [];
		$emptySubset = function()
		{
			return [null, new FastPriorityQueue()];
		};
		//Gather annotation data
		foreach ($annotations as $annotation)
		{
			switch (true)
			{
				case ($annotation instanceof DTA\Strategy):
					$subset = $subsets[$annotation->subset] ?? $emptySubset();
					if ($subset[0] !== null)
					{
						throw new \LogicException(\sprintf('Duplicate strategy annotation for metadata subset "%s" of class %s', $annotation->subset, $className));
					}
					$subset[0] = [$annotation->name, $annotation->options];
					$subsets[$annotation->subset] = $subset;
					break;
				case ($annotation instanceof DTA\Validator):
					$subset = $subsets[$annotation->subset] ?? $emptySubset();
					$subset[1]->insert([$annotation->name, $annotation->options, $annotation->blocker], $annotation->priority);
					$subsets[$annotation->subset] = $subset;
					break;
			}
		}
		//Create class subsets required by property annotations
		foreach ($propertySubsetNames as $propertySubsetName)
		{
			$subset = $subsets[$propertySubsetName] ?? $emptySubset();
			if ($subset[0] !== null)
			{
				throw new \LogicException(\sprintf('Excessive strategy annotation for metadata subset "%s" of class %s', $annotation->subset, $className));
			}
			$subset[0] = [Strategy\FieldData::class, ['type' => $className, 'subset' => $propertySubsetName]];
			$subset[1]->insert([Validator\FieldData::class, ['type' => $className, 'subset' => $propertySubsetName], false], self::MAX_VALIDATOR_PRIORITY);
			$subsets[$propertySubsetName] = $subset;
		}
		//Fulfil and emit gathered annotation data
		foreach ($subsets as $subset => [$strategy, $validatorQueue])
		{
			if ($strategy === null)
			{
				throw new \LogicException(\sprintf('No strategy annotation for metadata subset "%s" of class %s', $subset, $className));
			}
			$validator = [Validator\Chain::class, ['links' => $validatorQueue->toArray()]];
			yield [$subset, $strategy, $validator];
		}
	}

	/**
	 * @param \ReflectionClass $classReflection
	 * @param \ReflectionProperty $propertyReflection
	 * @param iterable $annotations
	 * @return \Generator tuples (<subset>, <field declaration>, <strategy declaration>, <validator declaration>)
	 * @psalm-return \Generator<array{0: string, 1: array{0: string, 1: null|array{0: string, 2: bool}, 2: null|array{0: string, 2: bool}}, 2: array{0: string, 1: null|array}, 3: array{0: string, 1: array}}>
	 * @throws \ReflectionException
	 */
	protected function processPropertyAnnotations(\ReflectionClass $classReflection, \ReflectionProperty $propertyReflection, iterable $annotations): \Generator
	{
		/** @psalm-var array<string, array{0: array{0: string, 1: null|array{0: string, 2: bool}, 2: null|array{0: string, 2: bool}}, 1: array{0: string, 1: null|array}, 2: FastPriorityQueue }}> $subsets */
		/** @var array|array[][]|FastPriorityQueue[][] $subsets */
		$subsets = [];
		$emptySubset = function()
		{
			return [null, null, new FastPriorityQueue()];
		};
		//Gather annotation data
		foreach ($annotations as $annotation)
		{
			switch (true)
			{
				case ($annotation instanceof DTA\Data):
					$subset = $subsets[$annotation->subset] ?? $emptySubset();
					if ($subset[0] !== null)
					{
						throw new \LogicException(\sprintf(
							'Duplicate data annotation for property %s in metadata subset "%s" of class %s',
							$propertyReflection->getName(), $annotation->subset, $classReflection->getName()
						));
					}
					$subset[0] = [
						$annotation->field ?? $propertyReflection->getName(),
						$this->calculatePropertyGetter($classReflection, $propertyReflection, $annotation),
						$this->calculatePropertySetter($classReflection, $propertyReflection, $annotation),
					];
					if (!$annotation->nullable)
					{
						$subset[2]->insert([Validator\NotNull::class, null, true], self::MAX_VALIDATOR_PRIORITY);
					}
					$subsets[$annotation->subset] = $subset;
					break;
				case ($annotation instanceof DTA\Strategy):
					$subset = $subsets[$annotation->subset] ?? $emptySubset();
					if ($subset[1] !== null)
					{
						throw new \LogicException(\sprintf(
							'Duplicate strategy annotation for property %s in metadata subset "%s" of class %s',
							$propertyReflection->getName(), $annotation->subset, $classReflection->getName()
						));
					}
					$subset[1] = [$annotation->name, $annotation->options];
					$subsets[$annotation->subset] = $subset;
					break;
				case ($annotation instanceof DTA\Validator):
					$subset = $subsets[$annotation->subset] ?? $emptySubset();
					$subset[2]->insert([$annotation->name, $annotation->options, $annotation->blocker], $annotation->priority);
					$subsets[$annotation->subset] = $subset;
					break;
			}
		}
		//Fulfil and emit gathered annotation data
		foreach ($subsets as $subset => [$field, $strategy, $validatorQueue])
		{
			if ($field === null)
			{
				throw new \LogicException(\sprintf(
					'No data annotation for property %s in metadata subset "%s" of class %s',
					$propertyReflection->getName(), $subset, $classReflection->getName()
				));
			}
			$strategy = $strategy ?? [Strategy\Whatever::class, null];
			$validator = [Validator\Chain::class, ['links' => $validatorQueue->toArray()]];
			yield [$subset, $field, $strategy, $validator];
		}
	}

	/**
	 * @param \ReflectionClass $classReflection
	 * @param \ReflectionProperty $propertyReflection
	 * @param DTA\Data $annotation
	 * @return null|array information about getter - tuple (<name of property or method>, <flag if getter is method>)
	 * @psalm-return null|array{0: string, 1: bool}
	 * @throws \ReflectionException
	 */
	protected function calculatePropertyGetter(\ReflectionClass $classReflection, \ReflectionProperty $propertyReflection, DTA\Data $annotation): ?array
	{
		$result = null;
		if ($annotation->getter !== '')
		{
			$name = $annotation->getter;
			$isMethod = true;
			if ($name === null)
			{
				if ($propertyReflection->isPublic())
				{
					$name = $propertyReflection->getName();
					$isMethod = false;
				}
				else
				{
					$name = 'get' . \str_replace('_', '', \ucwords($propertyReflection->getName(), '_'));
				}
			}
			//Validate method
			if ($isMethod)
			{
				if (!$classReflection->hasMethod($name))
				{
					throw new \LogicException(
						\sprintf('Invalid metadata for %s: no getter %s.', $classReflection->getName(), $name)
					);
				}
				$getterReflection = $classReflection->getMethod($name);
				if (!$getterReflection->isPublic())
				{
					throw new \LogicException(
						\sprintf('Invalid metadata for %s: getter %s is not public.', $classReflection->getName(), $name)
					);
				}
				if ($getterReflection->getNumberOfRequiredParameters() > 0)
				{
					throw new \LogicException(
						\sprintf('Invalid metadata for %s: getter %s should not require parameters.', $classReflection->getName(), $name)
					);
				}
			}
			$result = [$name, $isMethod];
		}
		return $result;
	}

	/**
	 * @param \ReflectionClass $classReflection
	 * @param \ReflectionProperty $propertyReflection
	 * @param DTA\Data $annotation
	 * @return null|array information about setter - tuple (<name of property or method>, <flag if setter is method>)
	 * @psalm-return null|array{0: string, 1: bool}
	 * @throws \ReflectionException
	 */
	protected function calculatePropertySetter(\ReflectionClass $classReflection, \ReflectionProperty $propertyReflection, DTA\Data $annotation): ?array
	{
		$result = null;
		if ($annotation->setter !== '')
		{
			$name = $annotation->setter;
			$isMethod = true;
			if ($name === null)
			{
				if ($propertyReflection->isPublic())
				{
					$name = $propertyReflection->getName();
					$isMethod = false;
				}
				else
				{
					$name = 'set' . \str_replace('_', '', \ucwords($propertyReflection->getName(), '_'));
				}
			}
			//Validate method
			if ($isMethod)
			{
				if (!$classReflection->hasMethod($name))
				{
					throw new \LogicException(
						\sprintf('Invalid metadata for %s: no setter %s.', $classReflection->getName(), $name)
					);
				}
				$setterReflection = $classReflection->getMethod($name);
				if (!$setterReflection->isPublic())
				{
					throw new \LogicException(
						\sprintf('Invalid metadata for %s: setter %s is not public.', $classReflection->getName(), $name)
					);
				}
				if ($setterReflection->getNumberOfParameters() < 1)
				{
					throw new \LogicException(
						\sprintf('Invalid metadata for %s: setter %s should accept at least one parameter.', $classReflection->getName(), $name)
					);
				}
				if ($setterReflection->getNumberOfRequiredParameters() > 1)
				{
					throw new \LogicException(
						\sprintf('Invalid metadata for %s: setter %s requires too many parameters.', $classReflection->getName(), $name)
					);
				}
			}
			$result = [$name, $isMethod];
		}
		return $result;
	}
}
