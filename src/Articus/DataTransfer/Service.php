<?php
namespace Articus\DataTransfer;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\ValidatorPluginManager;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

/**
 * Validating
 */
class Service
{
	/** @var Metadata\Reader\Annotation */
	protected $metadataReader;
	/** @var Strategy\PluginManager */
	protected $strategyPluginManager;
	/** @var ValidatorPluginManager */
	protected $validatorPluginManager;

	/**
	 * Internal hydrator cache
	 * @var Hydrator[][]
	 */
	protected $hydrators = [];

	/**
	 * Internal validator cache
	 * @var Validator[][]
	 */
	protected $validators = [];

	/**
	 * Service constructor.
	 * @param CacheStorage $metadataCacheStorage
	 * @param Strategy\PluginManager $strategyPluginManager
	 * @param ValidatorPluginManager $validatorPluginManager
	 */
	public function __construct(
		Metadata\Reader\Annotation $metadataReader,
		Strategy\PluginManager $strategyPluginManager,
		ValidatorPluginManager $validatorPluginManager
	)
	{
		//TODO replace with interface if there will be any other readers
		$this->metadataReader = $metadataReader;
		$this->strategyPluginManager = $strategyPluginManager;
		$this->validatorPluginManager = $validatorPluginManager;
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Data.php');
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Strategy.php');
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Validator.php');
	}

	/**
	 * @return Metadata\Reader\Annotation
	 */
	public function getMetadataReader()
	{
		return $this->metadataReader;
	}

	/**
	 * @param Metadata\Reader\Annotation $metadataReader
	 * @return self
	 */
	public function setMetadataReader(Metadata\Reader\Annotation $metadataReader)
	{
		$this->metadataReader = $metadataReader;
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
	 * @param string $fromSubset
	 * @param string $toSubset
	 * @return array - validation messages if transfer failed
	 */
	public function transfer($fromObjectOrArray, &$toObjectOrArray, $mapper = null, $fromSubset = '', $toSubset = '')
	{
		$result = [];
		$data = null;

		//Extract data array
		switch (true)
		{
			case is_object($fromObjectOrArray):
				$fromMetadata = $this->metadataReader->getMetadata(get_class($fromObjectOrArray), $fromSubset);
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
				$toMetadata = $this->metadataReader->getMetadata(get_class($toObjectOrArray), $toSubset);
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
	 * Returns hydrator required by metadata
	 * @param Metadata $metadata
	 * @return Hydrator
	 */
	public function getHydrator(Metadata $metadata)
	{
		if (!isset($this->hydrators[$metadata->className][$metadata->subset]))
		{
			$hydrator = new Hydrator();
			$hydrator
				->setStrategyPluginManager($this->strategyPluginManager)
				->setMetadata($metadata)
			;
			$this->hydrators[$metadata->className][$metadata->subset] = $hydrator;
		}
		return $this->hydrators[$metadata->className][$metadata->subset];
	}

	/**
	 * Returns validator required by metadata
	 * @param Metadata $metadata
	 * @return Validator
	 */
	public function getValidator(Metadata $metadata)
	{
		if (!isset($this->validators[$metadata->className][$metadata->subset]))
		{
			$validator = new Validator();
			$validator
				->setValidatorPluginManager($this->validatorPluginManager)
				->setMetadata($metadata)
			;
			$this->validators[$metadata->className][$metadata->subset] = $validator;
		}
		return $this->validators[$metadata->className][$metadata->subset];
	}

	/**
	 * Simple data transfer from one array to the other
	 * @param array $a
	 * @param array $b
	 * @return array
	 */
	static public function arrayTransfer(array $a, array $b)
	{
		if (ArrayUtils::isList($b, ArrayUtils::isList($a)))
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