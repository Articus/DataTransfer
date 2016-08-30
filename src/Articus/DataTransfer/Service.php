<?php
namespace Articus\DataTransfer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\ValidatorPluginManager;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

/**
 * Validating
 */
class Service
{
	/** @var CacheStorage */
	protected $metadataCacheStorage;
	/** @var Strategy\PluginManager */
	protected $strategyPluginManager;
	/** @var ValidatorPluginManager */
	protected $validatorPluginManager;

	/**
	 * Internal hydrator cache
	 * @var Hydrator[]
	 */
	protected $hydrators = [];

	/**
	 * Internal validator cache
	 * @var Validator[]
	 */
	protected $validators = [];

	/**
	 * Service constructor.
	 * @param CacheStorage $metadataCacheStorage
	 * @param Strategy\PluginManager $strategyPluginManager
	 * @param ValidatorPluginManager $validatorPluginManager
	 */
	public function __construct(
		CacheStorage $metadataCacheStorage,
		Strategy\PluginManager $strategyPluginManager,
		ValidatorPluginManager $validatorPluginManager
	)
	{
		//TODO check storage capabilities
		$this->metadataCacheStorage = $metadataCacheStorage;
		$this->strategyPluginManager = $strategyPluginManager;
		$this->validatorPluginManager = $validatorPluginManager;
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Data.php');
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Strategy.php');
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Validator.php');
	}

	/**
	 * @return CacheStorage
	 */
	public function getMetadataCacheStorage()
	{
		return $this->metadataCacheStorage;
	}

	/**
	 * @param CacheStorage $metadataCacheStorage
	 * @return self
	 */
	public function setMetadataCacheStorage(CacheStorage $metadataCacheStorage)
	{
		$this->metadataCacheStorage = $metadataCacheStorage;
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
	public function setStrategyPluginManager(Strategy\PluginManager $strategyPluginManager)
	{
		$this->strategyPluginManager = $strategyPluginManager;
		return $this;
	}

	/**
	 * @return ValidatorPluginManager
	 */
	public function getValidatorPluginManager()
	{
		return $this->validatorPluginManager;
	}

	/**
	 * @param ValidatorPluginManager $validatorPluginManager
	 * @return self
	 */
	public function setValidatorPluginManager(ValidatorPluginManager $validatorPluginManager)
	{
		$this->validatorPluginManager = $validatorPluginManager;
		return $this;
	}

	/**
	 * Transfers data from source to destination safely.
	 * @param array|object $fromObjectOrArray
	 * @param array|object $toObjectOrArray
	 * @param Mapper\MapperInterface|callable $mapper
	 * @return array - validation messages if transfer failed
	 */
	public function transfer($fromObjectOrArray, &$toObjectOrArray, $mapper = null)
	{
		$result = [];
		$data = null;

		//Extract data array
		switch (true)
		{
			case is_object($fromObjectOrArray):
				$fromMetadata = $this->getMetadata(get_class($fromObjectOrArray));
				$fromHydrator = $this->getHydrator($fromMetadata);
				$data = $fromHydrator->extract($fromObjectOrArray);
				break;
			case is_array($fromObjectOrArray):
				$data = &$fromObjectOrArray;
				break;
			default:
				throw new \InvalidArgumentException('Data transfer is possible only from object or array.');
		}

		//Map data array
		if (is_callable($mapper) || ($mapper instanceof Mapper\MapperInterface))
		{
			$data = $mapper($data);
			if (!is_array($data))
			{
				throw new \LogicException(
					sprintf('Invalid mapping: expecting array result, not %s.', gettype($data))
				);
			}
		}

		//Validate and hydrate data array
		switch (true)
		{
			case is_object($toObjectOrArray):
				$toMetadata = $this->getMetadata(get_class($toObjectOrArray));
				$toHydrator = $this->getHydrator($toMetadata);
				$validator = $this->getValidator($toMetadata);

				$result = $validator->validate(self::arrayTransfer($toHydrator->extract($toObjectOrArray), $data));
				if (empty($result))
				{
					$toObjectOrArray = $toHydrator->hydrate($data, $toObjectOrArray);
				}
				break;
			case is_array($toObjectOrArray):
				$toObjectOrArray = self::arrayTransfer($toObjectOrArray, $data);
				break;
			default:
				throw new \InvalidArgumentException('Data transfer is possible only to object or array.');
		}

		return $result;
	}

	/**
	 * Returns metadata for specified object
	 * @param string $className
	 * @return Metadata
	 */
	public function getMetadata($className)
	{
		$metadataCacheKey = $this->getClassMetadataCacheKey($className);
		$metadata = $this->metadataCacheStorage->getItem($metadataCacheKey);
		if ($metadata === null)
		{
			$metadata = $this->readClassMetadata($className);
			$this->metadataCacheStorage->addItem($metadataCacheKey, $metadata);
		}
		if (!($metadata instanceof Metadata))
		{
			throw new \LogicException(sprintf('Invalid metadata for class %s.', $className));
		}
		return $metadata;
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
	 * @return Metadata
	 */
	protected function readClassMetadata($className)
	{
		$result = new Metadata();
		$result->className = $className;

		$reflection = new \ReflectionClass($className);
		$reader = new AnnotationReader();

		//Read class annotations
		foreach ($reader->getClassAnnotations($reflection) as $annotation)
		{
			switch (true)
			{
				case ($annotation instanceof Annotation\Validator):
					$result->validators[Validator::GLOBAL_VALIDATOR_KEY][] = $annotation;
					break;
			}
		}

		//Read property annotations
		$propertyFilter = \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE;
		foreach ($reflection->getProperties($propertyFilter) as $property)
		{
			$data = null;
			$strategy = null;
			$validators = [];
			foreach ($reader->getPropertyAnnotations($property) as $annotation)
			{
				switch (true)
				{
					case ($annotation instanceof Annotation\Data):
						$data = $annotation;
						break;
					case ($annotation instanceof Annotation\Strategy):
						$strategy = $annotation;
						break;
					case ($annotation instanceof Annotation\Validator):
						$validators[] = $annotation;
						break;
				}
			}
			if ($data !== null)
			{
				//Process field name
				$field = (empty($data->field)? $property->getName() : $data->field);
				if (in_array($field, $result->fields))
				{
					throw new \LogicException(
						sprintf('Invalid metadata for %s: duplicate field %s.', $className, $field)
					);
				}
				$result->fields[] = $field;
				//Process direct access property
				if ($property->isPublic())
				{
					$result->properties[$field] = $property->getName();
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
				if (!empty($data->getter))
				{
					if (!$reflection->hasMethod($data->getter))
					{
						throw new \LogicException(
							sprintf('Invalid metadata for %s: no getter %s.', $className, $data->getter)
						);
					}
					$getter = $reflection->getMethod($data->getter);
					if (!$getter->isPublic())
					{
						throw new \LogicException(
							sprintf('Invalid metadata for %s: getter %s is not public.', $className, $data->getter)
						);
					}
					if ($getter->getNumberOfRequiredParameters() > 0)
					{
						throw new \LogicException(
							sprintf('Invalid metadata for %s: getter %s should not require parameters.', $className, $data->getter)
						);
					}
					$result->getters[$field] = $getter->getName();
				}
				//Process property setter
				if (!empty($data->setter))
				{
					if (!$reflection->hasMethod($data->setter))
					{
						throw new \LogicException(
							sprintf('Invalid metadata for %s: no setter %s.', $className, $data->setter)
						);
					}
					$setter = $reflection->getMethod($data->setter);
					if (!$setter->isPublic())
					{
						throw new \LogicException(
							sprintf('Invalid metadata for %s: setter %s is not public.', $className, $data->setter)
						);
					}
					if ($setter->getNumberOfParameters() < 1)
					{
						throw new \LogicException(
							sprintf('Invalid metadata for %s: setter %s should accept at least one parameter.', $className, $data->setter)
						);
					}
					if ($setter->getNumberOfRequiredParameters() > 1)
					{
						throw new \LogicException(
							sprintf('Invalid metadata for %s: setter %s requires too many parameters.', $className, $data->setter)
						);
					}
					$result->setters[$field] = $setter->getName();
				}
				//Copy strategy
				$result->strategies[$field] = $strategy;
				//Process nullable flag
				$result->nullables[$field] = $data->nullable;
				//Copy validators
				$result->validators[$field] = $validators;
			}
		}

		return $result;
	}

	/**
	 * Returns hydrator required by metadata
	 * @param Metadata $metadata
	 * @return Hydrator
	 */
	public function getHydrator(Metadata $metadata)
	{
		if (!isset($this->hydrators[$metadata->className]))
		{
			$hydrator = new Hydrator();
			$hydrator
				->setStrategyPluginManager($this->strategyPluginManager)
				->setMetadata($metadata)
			;
			$this->hydrators[$metadata->className] = $hydrator;
		}
		return $this->hydrators[$metadata->className];
	}

	/**
	 * Returns validator required by metadata
	 * @param Metadata $metadata
	 * @return Validator
	 */
	public function getValidator(Metadata $metadata)
	{
		if (!isset($this->validators[$metadata->className]))
		{
			$validator = new Validator();
			$validator
				->setValidatorPluginManager($this->validatorPluginManager)
				->setMetadata($metadata)
			;
			$this->validators[$metadata->className] = $validator;
		}
		return $this->validators[$metadata->className];
	}

	/**
	 * Simple data transfer from one array to the other
	 * @param array $a
	 * @param array $b
	 * @return array
	 */
	static public function arrayTransfer(array $a, array $b)
	{
		if (ArrayUtils::isList($b))
		{
			$a = $b;
		}
		else
		{
			foreach ($b as $key => $value)
			{
				if (array_key_exists($key, $a) && is_array($value) && is_array($a[$key]))
				{
					$a[$key] = self::arrayTransfer($a[$key], $value);
				}
				else
				{
					$a[$key] = $value;
				}
			}
		}
		return $a;
	}
}