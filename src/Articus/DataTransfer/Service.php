<?php
namespace Articus\DataTransfer;

/**
 * Service that performs data transfer.
 * Here "transfer" means merging source data to destination data only if destination data remains valid after that.
 */
class Service
{
	/**
	 * @var ClassMetadataProviderInterface
	 */
	protected $metadataProvider;

	/**
	 * @var Strategy\PluginManager
	 */
	protected $strategyManager;

	/**
	 * @var Validator\PluginManager
	 */
	protected $validatorManager;

	/**
	 * @var Strategy\HydratorInterface
	 */
	protected $untypedDataHydrator;

	/**
	 * @param ClassMetadataProviderInterface $metadataProvider
	 * @param Strategy\PluginManager $strategyManager
	 * @param Validator\PluginManager $validatorManager
	 * @param Strategy\HydratorInterface $untypedDataHydrator
	 */
	public function __construct(
		ClassMetadataProviderInterface $metadataProvider,
		Strategy\PluginManager $strategyManager,
		Validator\PluginManager $validatorManager,
		Strategy\HydratorInterface $untypedDataHydrator
	)
	{
		$this->metadataProvider = $metadataProvider;
		$this->strategyManager = $strategyManager;
		$this->validatorManager = $validatorManager;
		$this->untypedDataHydrator = $untypedDataHydrator;
	}

	/**
	 * Transfers data from source to destination using specified extractors, validator and hydrator
	 * @param mixed $from source of data
	 * @param Strategy\ExtractorInterface $fromExtractor a way to extract untyped data from source
	 * @param mixed $to destination for data
	 * @param Strategy\ExtractorInterface $toExtractor a way to extract untyped data from destination
	 * @param Validator\ValidatorInterface $toValidator a way to validate untyped data for destination
	 * @param Strategy\HydratorInterface $toHydrator a way to hydrate untyped data to destination
	 * @return array list of violations found during data validation
	 */
	public function transfer(
		$from,
		Strategy\ExtractorInterface $fromExtractor,
		&$to,
		Strategy\ExtractorInterface $toExtractor,
		Validator\ValidatorInterface $toValidator,
		Strategy\HydratorInterface $toHydrator
	): array
	{
		$result = [];
		try
		{
			$fromData = $fromExtractor->extract($from);
			$toData = $toExtractor->extract($to);
			$this->untypedDataHydrator->hydrate($fromData, $toData);
			$result = $toValidator->validate($toData);
			if (empty($result))
			{
				$toHydrator->hydrate($fromData, $to);
			}
		}
		catch (Exception\InvalidData $e)
		{
			$result = $e->getViolations();
		}

		return $result;
	}

	/**
	 * Transfers data from typed source to typed destination
	 * @param object $from source of typed data
	 * @param object $to destination for typed data
	 * @param string $fromSubset name of the subset to filter data extracted from source
	 * @param string $toSubset name of the subset to limit data hydrated to destination
	 * @return array list of violations found during data validation
	 */
	public function transferTypedData($from, &$to, string $fromSubset = '', string $toSubset = ''): array
	{
		$fromStrategy = $this->getTypedDataStrategy($from, $fromSubset);
		$toStrategy = $this->getTypedDataStrategy($to, $toSubset);
		$toValidator = $this->getTypedDataValidator($to, $toSubset);

		return $this->transfer($from, $fromStrategy, $to, $toStrategy, $toValidator, $toStrategy);
	}

	/**
	 * Transfers data from typed source to untyped destination
	 * @param object $typedData source of typed data
	 * @param mixed $untypedData destination for untyped data
	 * @param string $subset name of the subset to filter data extracted from source
	 * @return array list of violations found during data validation
	 */
	public function transferFromTypedData($typedData, &$untypedData, string $subset = ''): array
	{
		$strategy = $this->getTypedDataStrategy($typedData, $subset);

		$result = [];
		try
		{
			$data = $strategy->extract($typedData);
			$this->untypedDataHydrator->hydrate($data, $untypedData);
		}
		catch (Exception\InvalidData $e)
		{
			$result = $e->getViolations();
		}

		return $result;
	}

	/**
	 * Transfers data from untyped source to typed destination
	 * @param mixed $untypedData source of untyped data
	 * @param object $typedData destination for typed data
	 * @param string $subset name of the subset to limit data hydrated to destination
	 * @return array list of violations found during data validation
	 */
	public function transferToTypedData($untypedData, &$typedData, string $subset = ''): array
	{
		$strategy = $this->getTypedDataStrategy($typedData, $subset);
		$validator = $this->getTypedDataValidator($typedData, $subset);

		$result = [];
		try
		{
			$data = $strategy->extract($typedData);
			$this->untypedDataHydrator->hydrate($untypedData, $data);
			$result = $validator->validate($data);
			if (empty($result))
			{
				$strategy->hydrate($untypedData, $typedData);
			}
		}
		catch (Exception\InvalidData $e)
		{
			$result = $e->getViolations();
		}

		return $result;
	}

	/**
	 * Extracts untyped data from typed source
	 * @param object $typedData source of typed data
	 * @param string $subset name of the subset to filter data extracted from source
	 * @return mixed extracted untyped data
	 * @throws Exception\InvalidData
	 */
	public function extractFromTypedData($typedData, string $subset = '')
	{
		$strategy = $this->getTypedDataStrategy($typedData, $subset);
		return $strategy->extract($typedData);
	}

	/**
	 * Returns strategy to transfer provided typed data according specific class metadata subset
	 * @param object $typedData
	 * @param string $subset
	 * @return Strategy\StrategyInterface
	 */
	public function getTypedDataStrategy($typedData, string $subset = ''): Strategy\StrategyInterface
	{
		if (!\is_object($typedData))
		{
			throw new \LogicException(\sprintf('Typed data should be object, not %s.', \gettype($typedData)));
		}
		return $this->strategyManager->get(...$this->metadataProvider->getClassStrategy(\get_class($typedData), $subset));
	}

	/**
	 * Returns validator for provided typed data according specific class metadata subset
	 * @param object $typedData
	 * @param string $subset
	 * @return Validator\ValidatorInterface
	 */
	public function getTypedDataValidator($typedData, string $subset = ''): Validator\ValidatorInterface
	{
		if (!\is_object($typedData))
		{
			throw new \LogicException(\sprintf('Typed data should be object, not %s.', \gettype($typedData)));
		}
		return $this->validatorManager->get(...$this->metadataProvider->getClassValidator(\get_class($typedData), $subset));
	}
}
