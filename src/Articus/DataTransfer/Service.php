<?php
declare(strict_types=1);

namespace Articus\DataTransfer;

use Articus\PluginManager\PluginManagerInterface;
use function get_class;

/**
 * Service that performs data transfer.
 * Here "transfer" means merging source data to destination data only if destination data remains valid after that.
 */
class Service
{
	protected ClassMetadataProviderInterface $metadataProvider;

	/**
	 * @var PluginManagerInterface<Strategy\StrategyInterface>
	 */
	protected PluginManagerInterface $strategyManager;

	/**
	 * @var PluginManagerInterface<Validator\ValidatorInterface>
	 */
	protected PluginManagerInterface $validatorManager;

	/**
	 * @param ClassMetadataProviderInterface $metadataProvider
	 * @param PluginManagerInterface<Strategy\StrategyInterface> $strategyManager
	 * @param PluginManagerInterface<Validator\ValidatorInterface> $validatorManager
	 */
	public function __construct(
		ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager
	)
	{
		$this->metadataProvider = $metadataProvider;
		$this->strategyManager = $strategyManager;
		$this->validatorManager = $validatorManager;
	}

	/**
	 * Transfers data from source to destination using specified extractors, validator and hydrator
	 * @param mixed $from source of data
	 * @param Strategy\ExtractorInterface $fromExtractor a way to extract untyped data from source
	 * @param mixed $to destination for data
	 * @param Strategy\ExtractorInterface $toExtractor a way to extract untyped data from destination
	 * @param Strategy\MergerInterface $merger a way to merge untyped data from source and destination
	 * @param Validator\ValidatorInterface $toValidator a way to validate merged untyped data
	 * @param Strategy\HydratorInterface $toHydrator a way to hydrate untyped data from source to destination
	 * @return array list of violations found during data validation
	 */
	public function transfer(
		$from,
		Strategy\ExtractorInterface $fromExtractor,
		&$to,
		Strategy\ExtractorInterface $toExtractor,
		Strategy\MergerInterface $merger,
		Validator\ValidatorInterface $toValidator,
		Strategy\HydratorInterface $toHydrator
	): array
	{
		$result = [];
		try
		{
			$fromData = $fromExtractor->extract($from);
			$toData = $toExtractor->extract($to);
			$merger->merge($fromData, $toData);
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
	public function transferTypedData(object $from, object &$to, string $fromSubset = '', string $toSubset = ''): array
	{
		$fromStrategy = $this->getTypedDataStrategy($from, $fromSubset);
		$toStrategy = $this->getTypedDataStrategy($to, $toSubset);
		$toValidator = $this->getTypedDataValidator($to, $toSubset);

		return $this->transfer($from, $fromStrategy, $to, $toStrategy, $toStrategy, $toValidator, $toStrategy);
	}

	/**
	 * Transfers data from untyped source to typed destination
	 * @param mixed $untypedData source of untyped data
	 * @param object $typedData destination for typed data
	 * @param string $subset name of the subset to limit data hydrated to destination
	 * @return array list of violations found during data validation
	 */
	public function transferToTypedData($untypedData, object &$typedData, string $subset = ''): array
	{
		$strategy = $this->getTypedDataStrategy($typedData, $subset);
		$validator = $this->getTypedDataValidator($typedData, $subset);

		$result = [];
		try
		{
			$data = $strategy->extract($typedData);
			$strategy->merge($untypedData, $data);
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
	public function extractFromTypedData(object $typedData, string $subset = '')
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
	public function getTypedDataStrategy(object $typedData, string $subset = ''): Strategy\StrategyInterface
	{
		return ($this->strategyManager)(...$this->metadataProvider->getClassStrategy(get_class($typedData), $subset));
	}

	/**
	 * Returns validator for provided typed data according specific class metadata subset
	 * @param object $typedData
	 * @param string $subset
	 * @return Validator\ValidatorInterface
	 */
	public function getTypedDataValidator(object $typedData, string $subset = ''): Validator\ValidatorInterface
	{
		return ($this->validatorManager)(...$this->metadataProvider->getClassValidator(get_class($typedData), $subset));
	}
}
