<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataProvider;

use Articus\DataTransfer\MetadataCache\MetadataCacheInterface;
use Articus\DataTransfer\PhpAttribute as DTA;
use Articus\DataTransfer\ClassMetadataProviderInterface;
use Articus\DataTransfer\FieldMetadataProviderInterface;
use Articus\DataTransfer\Strategy;
use Articus\DataTransfer\Validator;
use Generator;
use Laminas\Stdlib\FastPriorityQueue;
use LogicException;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionProperty;
use function array_keys;
use function sprintf;
use function str_replace;
use function ucwords;

/**
 * Provider that retrieves metadata from class PHP attributes
 */
class PhpAttribute implements ClassMetadataProviderInterface, FieldMetadataProviderInterface
{
	const MAX_VALIDATOR_PRIORITY = 10000;

	protected MetadataCacheInterface $cache;

	/**
	 * @psalm-var array<string, array<string, array{0: string, 1: array}>>
	 */
	protected array $classStrategies = [];

	/**
	 * @psalm-var array<string, array<string, array{0: string, 1: array}>>
	 */
	protected array $classValidators = [];

	/**
	 * @psalm-var array<string, array<string, array<string, array{0: string, 1: null|array{0: string, 1: bool}, 2: null|array{0: string, 1: bool}}>>>
	 */
	protected array $classFields = [];

	/**
	 * @psalm-var array<string, array<string, array<string, array{0: string, 1: array}>>>
	 */
	protected array $fieldStrategies = [];

	/**
	 * @psalm-var array<string, array<string, array<string, array{0: string, 1: array}>>>
	 */
	protected array $fieldValidators = [];

	public function __construct(MetadataCacheInterface $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * @inheritDoc
	 */
	public function getClassStrategy(string $className, string $subset): array
	{
		$this->ascertainMetadata($className);
		$result = $this->classStrategies[$className][$subset] ?? null;
		if ($result === null)
		{
			throw new LogicException(sprintf('No strategy for metadata subset "%s" of class %s', $subset, $className));
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getClassValidator(string $className, string $subset): array
	{
		$this->ascertainMetadata($className);
		$result = $this->classValidators[$className][$subset] ?? null;
		if ($result === null)
		{
			throw new LogicException(sprintf('No validator for metadata subset "%s" of class %s', $subset, $className));
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getClassFields(string $className, string $subset): iterable
	{
		$this->ascertainMetadata($className);
		$fields = $this->classFields[$className][$subset] ?? null;
		if ($fields === null)
		{
			throw new LogicException(sprintf('No fields for metadata subset "%s" of class %s', $subset, $className));
		}
		yield from $fields;
	}

	/**
	 * @inheritDoc
	 */
	public function getFieldStrategy(string $className, string $subset, string $fieldName): array
	{
		$this->ascertainMetadata($className);
		$result = $this->fieldStrategies[$className][$subset][$fieldName] ?? null;
		if ($result === null)
		{
			throw new LogicException(sprintf('No strategy for field %s in metadata subset "%s" of class %s', $fieldName, $subset, $className));
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getFieldValidator(string $className, string $subset, string $fieldName): array
	{
		$this->ascertainMetadata($className);
		$result = $this->fieldValidators[$className][$subset][$fieldName] ?? null;
		if ($result === null)
		{
			throw new LogicException(sprintf('No validator for field %s in metadata subset "%s" of class %s', $fieldName, $subset, $className));
		}
		return $result;
	}

	/**
	 * Ascertains that metadata for specified class was loaded
	 * @param class-string $className
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
	 * @param class-string $className
	 * @return array
	 */
	protected function loadMetadata(string $className): array
	{
		$classStrategies = [];
		$classValidators = [];
		$classFields = [];
		$fieldStrategies = [];
		$fieldValidators = [];

		$classReflection = new ReflectionClass($className);
		//Read property PHP attributes
		$propertyFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;
		foreach ($classReflection->getProperties($propertyFilter) as $propertyReflection)
		{
			foreach ($this->processPhpAttributesForProperty($classReflection, $propertyReflection) as [$subset, $field, $strategy, $validator])
			{
				$fieldName = $field[0];
				if (!empty($classFields[$subset][$fieldName]))
				{
					throw new LogicException(sprintf('Duplicate field "%s" declaration for subset %s of class %s', $fieldName, $subset, $className));
				}
				$classFields[$subset][$fieldName] = $field;
				$fieldStrategies[$subset][$fieldName] = $strategy;
				$fieldValidators[$subset][$fieldName] = $validator;
			}
		}
		//Read class PHP attributes
		$propertySubsets = array_keys($classFields);
		foreach ($this->processPhpAttributesForClass($classReflection, $propertySubsets) as [$subset, $strategy, $validator])
		{
			$classStrategies[$subset] = $strategy;
			$classValidators[$subset] = $validator;
		}

		return [$classStrategies, $classValidators, $classFields, $fieldStrategies, $fieldValidators];
	}

	/**
	 * @param ReflectionClass $classReflection
	 * @param iterable $propertySubsetNames
	 * @return Generator tuples (<subset>, <strategy declaration>, <validator declaration>)
	 * @psalm-return Generator<array{0: string, 1: array{0: string, 1: null|array}, 2: array{0: string, 1: array}}>
	 */
	protected function processPhpAttributesForClass(ReflectionClass $classReflection, iterable $propertySubsetNames): Generator
	{
		$className = $classReflection->getName();
		/** @psalm-var array<string, array{0: array{0: string, 1: null|array}, 1: FastPriorityQueue<array{0: string, 1: array, 2: bool}>}> $subsets */
		/** @var array|array[][]|FastPriorityQueue[][] $subsets */
		$subsets = [];
		$emptySubset = function()
		{
			return [null, new FastPriorityQueue()];
		};
		//Gather PHP attribute data
		foreach ($classReflection->getAttributes() as $attributeReflection)
		{
			$phpAttribute = match ($attributeReflection->getName())
			{
				DTA\Strategy::class, DTA\Validator::class => $attributeReflection->newInstance(),
				default => null,
			};
			switch (true)
			{
				case ($phpAttribute instanceof DTA\Strategy):
					$subset = $subsets[$phpAttribute->subset] ?? $emptySubset();
					if ($subset[0] !== null)
					{
						throw new LogicException(sprintf('Duplicate strategy PHP attribute for metadata subset "%s" of class %s', $phpAttribute->subset, $className));
					}
					$subset[0] = [$phpAttribute->name, $phpAttribute->options];
					$subsets[$phpAttribute->subset] = $subset;
					break;
				case ($phpAttribute instanceof DTA\Validator):
					$subset = $subsets[$phpAttribute->subset] ?? $emptySubset();
					$subset[1]->insert([$phpAttribute->name, $phpAttribute->options, $phpAttribute->blocker], $phpAttribute->priority);
					$subsets[$phpAttribute->subset] = $subset;
					break;
			}
		}
		//Create class subsets required by property metadata
		foreach ($propertySubsetNames as $propertySubsetName)
		{
			$subset = $subsets[$propertySubsetName] ?? $emptySubset();
			if ($subset[0] !== null)
			{
				throw new LogicException(sprintf('Excessive strategy PHP attribute for metadata subset "%s" of class %s', $propertySubsetName, $className));
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
				throw new LogicException(sprintf('No strategy PHP attribute for metadata subset "%s" of class %s', $subset, $className));
			}
			$validator = [Validator\Chain::class, ['links' => $validatorQueue->toArray()]];
			yield [$subset, $strategy, $validator];
		}
	}

	/**
	 * @param ReflectionClass $classReflection
	 * @param ReflectionProperty $propertyReflection
	 * @return Generator tuples (<subset>, <field declaration>, <strategy declaration>, <validator declaration>)
	 * @psalm-return Generator<array{0: string, 1: array{0: string, 1: null|array{0: string, 2: bool}, 2: null|array{0: string, 2: bool}}, 2: array{0: string, 1: null|array}, 3: array{0: string, 1: array}}>
	 */
	protected function processPhpAttributesForProperty(ReflectionClass $classReflection, ReflectionProperty $propertyReflection): Generator
	{
		/** @psalm-var array<string, array{0: array{0: string, 1: null|array{0: string, 2: bool}, 2: null|array{0: string, 2: bool}}, 1: array{0: string, 1: null|array}, 2: FastPriorityQueue}> $subsets */
		/** @var array|array[][]|FastPriorityQueue[][] $subsets */
		$subsets = [];
		$emptySubset = function()
		{
			return [null, null, new FastPriorityQueue()];
		};
		//Gather phpAttribute data
		foreach ($propertyReflection->getAttributes() as $attributeReflection)
		{
			$phpAttribute = match ($attributeReflection->getName())
			{
				DTA\Data::class, DTA\Strategy::class, DTA\Validator::class => $attributeReflection->newInstance(),
				default => null,
			};
			switch (true)
			{
				case ($phpAttribute instanceof DTA\Data):
					$subset = $subsets[$phpAttribute->subset] ?? $emptySubset();
					if ($subset[0] !== null)
					{
						throw new LogicException(sprintf(
							'Duplicate data PHP attribute for property %s in metadata subset "%s" of class %s',
							$propertyReflection->getName(), $phpAttribute->subset, $classReflection->getName()
						));
					}
					$subset[0] = [
						$phpAttribute->field ?? $propertyReflection->getName(),
						$this->calculatePropertyGetter($classReflection, $propertyReflection, $phpAttribute),
						$this->calculatePropertySetter($classReflection, $propertyReflection, $phpAttribute),
					];
					if (!$phpAttribute->nullable)
					{
						$subset[2]->insert([Validator\NotNull::class, [], true], self::MAX_VALIDATOR_PRIORITY);
					}
					$subsets[$phpAttribute->subset] = $subset;
					break;
				case ($phpAttribute instanceof DTA\Strategy):
					$subset = $subsets[$phpAttribute->subset] ?? $emptySubset();
					if ($subset[1] !== null)
					{
						throw new LogicException(sprintf(
							'Duplicate strategy PHP attribute for property %s in metadata subset "%s" of class %s',
							$propertyReflection->getName(), $phpAttribute->subset, $classReflection->getName()
						));
					}
					$subset[1] = [$phpAttribute->name, $phpAttribute->options];
					$subsets[$phpAttribute->subset] = $subset;
					break;
				case ($phpAttribute instanceof DTA\Validator):
					$subset = $subsets[$phpAttribute->subset] ?? $emptySubset();
					$subset[2]->insert([$phpAttribute->name, $phpAttribute->options, $phpAttribute->blocker], $phpAttribute->priority);
					$subsets[$phpAttribute->subset] = $subset;
					break;
			}
		}
		//Fulfil and emit gathered phpAttribute data
		foreach ($subsets as $subset => [$field, $strategy, $validatorQueue])
		{
			if ($field === null)
			{
				throw new LogicException(sprintf(
					'No data PHP attribute for property %s in metadata subset "%s" of class %s',
					$propertyReflection->getName(), $subset, $classReflection->getName()
				));
			}
			$strategy = $strategy ?? [Strategy\Whatever::class, []];
			$validator = [Validator\Chain::class, ['links' => $validatorQueue->toArray()]];
			yield [$subset, $field, $strategy, $validator];
		}
	}

	/**
	 * @param ReflectionClass $classReflection
	 * @param ReflectionProperty $propertyReflection
	 * @param DTA\Data $phpAttribute
	 * @return null|array information about getter - tuple (<name of property or method>, <flag if getter is method>)
	 * @psalm-return null|array{0: string, 1: bool}
	 */
	protected function calculatePropertyGetter(ReflectionClass $classReflection, ReflectionProperty $propertyReflection, DTA\Data $phpAttribute): null|array
	{
		$result = null;
		if ($phpAttribute->getter !== '')
		{
			$name = $phpAttribute->getter;
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
					$name = 'get' . str_replace('_', '', ucwords($propertyReflection->getName(), '_'));
				}
			}
			//Validate method
			if ($isMethod)
			{
				if (!$classReflection->hasMethod($name))
				{
					throw new LogicException(
						sprintf('Invalid metadata for %s: no getter %s.', $classReflection->getName(), $name)
					);
				}
				$getterReflection = $classReflection->getMethod($name);
				if (!$getterReflection->isPublic())
				{
					throw new LogicException(
						sprintf('Invalid metadata for %s: getter %s is not public.', $classReflection->getName(), $name)
					);
				}
				if ($getterReflection->getNumberOfRequiredParameters() > 0)
				{
					throw new LogicException(
						sprintf('Invalid metadata for %s: getter %s should not require parameters.', $classReflection->getName(), $name)
					);
				}
			}
			$result = [$name, $isMethod];
		}
		return $result;
	}

	/**
	 * @param ReflectionClass $classReflection
	 * @param ReflectionProperty $propertyReflection
	 * @param DTA\Data $phpAttribute
	 * @return null|array information about setter - tuple (<name of property or method>, <flag if setter is method>)
	 * @psalm-return null|array{0: string, 1: bool}
	 */
	protected function calculatePropertySetter(ReflectionClass $classReflection, ReflectionProperty $propertyReflection, DTA\Data $phpAttribute): null|array
	{
		$result = null;
		if ($phpAttribute->setter !== '')
		{
			$name = $phpAttribute->setter;
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
					$name = 'set' . str_replace('_', '', ucwords($propertyReflection->getName(), '_'));
				}
			}
			//Validate method
			if ($isMethod)
			{
				if (!$classReflection->hasMethod($name))
				{
					throw new LogicException(
						sprintf('Invalid metadata for %s: no setter %s.', $classReflection->getName(), $name)
					);
				}
				$setterReflection = $classReflection->getMethod($name);
				if (!$setterReflection->isPublic())
				{
					throw new LogicException(
						sprintf('Invalid metadata for %s: setter %s is not public.', $classReflection->getName(), $name)
					);
				}
				if ($setterReflection->getNumberOfParameters() < 1)
				{
					throw new LogicException(
						sprintf('Invalid metadata for %s: setter %s should accept at least one parameter.', $classReflection->getName(), $name)
					);
				}
				if ($setterReflection->getNumberOfRequiredParameters() > 1)
				{
					throw new LogicException(
						sprintf('Invalid metadata for %s: setter %s requires too many parameters.', $classReflection->getName(), $name)
					);
				}
			}
			$result = [$name, $isMethod];
		}
		return $result;
	}
}
