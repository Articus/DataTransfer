<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer;

use Articus\DataTransfer as DT;

\describe(DT\Service::class, function ()
{
	\describe('->transfer', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('transfers valid data', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$fromExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$toExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$merger = \mock(DT\Strategy\MergerInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);
			$toHydrator = \mock(DT\Strategy\HydratorInterface::class);

			$from = 1;
			$to = 2;
			$extractedFrom = 3;
			$extractedTo = 4;
			$updatedExtractedTo = 5;
			$violations = [];
			$updatedTo = 6;

			$fromExtractor->shouldReceive('extract')->with($from)->once()->andReturn($extractedFrom);
			$toExtractor->shouldReceive('extract')->with($to)->once()->andReturn($extractedTo);
			$merger->shouldReceive('merge')->withArgs(
				function($a, &$b) use (&$extractedFrom, &$extractedTo, &$updatedExtractedTo)
				{
					$result = false;
					if (($a === $extractedFrom) && ($b === $extractedTo))
					{
						$result = true;
						$b = $updatedExtractedTo;
					}
					return $result;
				}
			)->once();
			$toValidator->shouldReceive('validate')->with($updatedExtractedTo)->once()->andReturn($violations);
			$toHydrator->shouldReceive('hydrate')->withArgs(
				function($a, &$b) use (&$extractedFrom, &$to, &$updatedTo)
				{
					$result = false;
					if (($a === $extractedFrom) && ($b === $to))
					{
						$result = true;
						$b = $updatedTo;
					}
					return $result;
				}
			)->once();

			$service = new DT\Service($metadataProvider, $strategyManager, $validatorManager);

			$transferResult = $service->transfer($from, $fromExtractor, $to, $toExtractor, $merger, $toValidator, $toHydrator);
			\expect($transferResult)->toBe($violations);
			\expect($to)->toBe($updatedTo);
		});
		\it('returns violations found during source extraction', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$fromExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$toExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$merger = \mock(DT\Strategy\MergerInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);
			$toHydrator = \mock(DT\Strategy\HydratorInterface::class);

			$from = 1;
			$to = 2;
			$originalTo = $to;
			$extractedFromError = new DT\Exception\InvalidData(['test' => 123]);

			$fromExtractor->shouldReceive('extract')->with($from)->once()->andThrow($extractedFromError);

			$service = new DT\Service($metadataProvider, $strategyManager, $validatorManager);

			$transferResult = $service->transfer($from, $fromExtractor, $to, $toExtractor, $merger, $toValidator, $toHydrator);
			\expect($transferResult)->toBe($extractedFromError->getViolations());
			\expect($to)->toBe($originalTo);
		});
		\it('returns violations found during destination extraction', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$fromExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$toExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$merger = \mock(DT\Strategy\MergerInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);
			$toHydrator = \mock(DT\Strategy\HydratorInterface::class);

			$from = 1;
			$to = 2;
			$extractedFrom = 3;
			$extractedToError = new DT\Exception\InvalidData(['test' => 123]);
			$originalTo = $to;

			$fromExtractor->shouldReceive('extract')->with($from)->once()->andReturn($extractedFrom);
			$toExtractor->shouldReceive('extract')->with($to)->once()->andThrow($extractedToError);

			$service = new DT\Service($metadataProvider, $strategyManager, $validatorManager);

			$transferResult = $service->transfer($from, $fromExtractor, $to, $toExtractor, $merger, $toValidator, $toHydrator);
			\expect($transferResult)->toBe($extractedToError->getViolations());
			\expect($to)->toBe($originalTo);
		});
		\it('returns violations found during merge of extracted source to extracted destination', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$fromExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$toExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$merger = \mock(DT\Strategy\MergerInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);
			$toHydrator = \mock(DT\Strategy\HydratorInterface::class);

			$from = 1;
			$to = 2;
			$extractedFrom = 3;
			$extractedTo = 4;
			$updatedExtractedToError = new DT\Exception\InvalidData(['test' => 123]);
			$originalTo = $to;

			$fromExtractor->shouldReceive('extract')->with($from)->once()->andReturn($extractedFrom);
			$toExtractor->shouldReceive('extract')->with($to)->once()->andReturn($extractedTo);
			$merger->shouldReceive('merge')->once()->andThrow($updatedExtractedToError);

			$service = new DT\Service($metadataProvider, $strategyManager, $validatorManager);

			$transferResult = $service->transfer($from, $fromExtractor, $to, $toExtractor, $merger, $toValidator, $toHydrator);
			\expect($transferResult)->toBe($updatedExtractedToError->getViolations());
			\expect($to)->toBe($originalTo);
		});
		\it('returns violations found during validation of updated extracted destination', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$fromExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$toExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$merger = \mock(DT\Strategy\MergerInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);
			$toHydrator = \mock(DT\Strategy\HydratorInterface::class);

			$from = 1;
			$to = 2;
			$extractedFrom = 3;
			$extractedTo = 4;
			$updatedExtractedTo = 5;
			$violations = ['test' => 123];
			$originalTo = $to;

			$fromExtractor->shouldReceive('extract')->with($from)->once()->andReturn($extractedFrom);
			$toExtractor->shouldReceive('extract')->with($to)->once()->andReturn($extractedTo);
			$merger->shouldReceive('merge')->withArgs(
				function($a, &$b) use (&$extractedFrom, &$extractedTo, &$updatedExtractedTo)
				{
					$result = false;
					if (($a === $extractedFrom) && ($b === $extractedTo))
					{
						$result = true;
						$b = $updatedExtractedTo;
					}
					return $result;
				}
			)->once();
			$toValidator->shouldReceive('validate')->with($updatedExtractedTo)->once()->andReturn($violations);

			$service = new DT\Service($metadataProvider, $strategyManager, $validatorManager);

			$transferResult = $service->transfer($from, $fromExtractor, $to, $toExtractor, $merger, $toValidator, $toHydrator);
			\expect($transferResult)->toBe($violations);
			\expect($to)->toBe($originalTo);
		});
		\it('returns violations found during hydration of extracted source to destination', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$fromExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$toExtractor = \mock(DT\Strategy\ExtractorInterface::class);
			$merger = \mock(DT\Strategy\MergerInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);
			$toHydrator = \mock(DT\Strategy\HydratorInterface::class);

			$from = 1;
			$to = 2;
			$extractedFrom = 3;
			$extractedTo = 4;
			$updatedExtractedTo = 5;
			$violations = [];
			$updatedToError = new DT\Exception\InvalidData(['test' => 123]);
			$originalTo = $to;

			$fromExtractor->shouldReceive('extract')->with($from)->once()->andReturn($extractedFrom);
			$toExtractor->shouldReceive('extract')->with($to)->once()->andReturn($extractedTo);
			$merger->shouldReceive('merge')->withArgs(
				function($a, &$b) use (&$extractedFrom, &$extractedTo, &$updatedExtractedTo)
				{
					$result = false;
					if (($a === $extractedFrom) && ($b === $extractedTo))
					{
						$result = true;
						$b = $updatedExtractedTo;
					}
					return $result;
				}
			)->once();
			$toValidator->shouldReceive('validate')->with($updatedExtractedTo)->once()->andReturn($violations);
			$toHydrator->shouldReceive('hydrate')->once()->andThrow($updatedToError);

			$service = new DT\Service($metadataProvider, $strategyManager, $validatorManager);

			$transferResult = $service->transfer($from, $fromExtractor, $to, $toExtractor, $merger, $toValidator, $toHydrator);
			\expect($transferResult)->toBe($updatedToError->getViolations());
			\expect($to)->toBe($originalTo);
		});
	});
	\describe('->transferTypedData', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('passes parameters for ->transfer from valid data', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$fromStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$toStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);

			$from = \mock();
			$to = \mock();
			$fromSubset = 'subset1';
			$toSubset = 'subset2';
			$violations = ['test' => 123];
			$updatedTo = \mock();

			$service = \mock(DT\Service::class, [$metadataProvider, $strategyManager, $validatorManager])->makePartial();
			$service->shouldReceive('transfer')->withArgs(
				function($a, $b, &$c, $d, $e, $f, $g) use (&$from, &$to, &$fromStrategy, &$toStrategy, &$toValidator, &$updatedTo)
				{
					$result =
						($a === $from)
						&& ($b === $fromStrategy)
						&& ($c === $to)
						&& ($d === $toStrategy)
						&& ($e === $toStrategy)
						&& ($f === $toValidator)
						&& ($g === $toStrategy)
					;
					if ($result)
					{
						$c = $updatedTo;
					}
					return $result;
				}
			)->once()->andReturn($violations);
			$service->shouldReceive('getTypedDataStrategy')->with($from, $fromSubset)->once()->andReturn($fromStrategy);
			$service->shouldReceive('getTypedDataStrategy')->with($to, $toSubset)->once()->andReturn($toStrategy);
			$service->shouldReceive('getTypedDataValidator')->with($to, $toSubset)->once()->andReturn($toValidator);

			/** @var DT\Service $service */
			$transferResult = $service->transferTypedData($from, $to, $fromSubset, $toSubset);
			\expect($transferResult)->toBe($violations);
			\expect($to)->toBe($updatedTo);
		});
	});
	\describe('->transferToTypedData', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('transfers valid data', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$toStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);

			$from = 1;
			$to = \mock();
			$subset = 'test';
			$extractedTo = 2;
			$updatedExtractedTo = 3;
			$violations = [];
			$updatedTo = 5;

			$toStrategy->shouldReceive('extract')->with($to)->once()->andReturn($extractedTo);
			$toStrategy->shouldReceive('merge')->withArgs(
				function($a, &$b) use (&$from, &$extractedTo, &$updatedExtractedTo)
				{
					$result = ($a === $from) && ($b === $extractedTo);
					if($result)
					{
						$b = $updatedExtractedTo;
					}
					return $result;
				}
			)->once();
			$toValidator->shouldReceive('validate')->with($updatedExtractedTo)->once()->andReturn($violations);
			$toStrategy->shouldReceive('hydrate')->withArgs(
				function($a, &$b) use (&$from, &$to, &$updatedTo)
				{
					$result = ($a === $from) && ($b === $to);
					if ($result)
					{
						$b = $updatedTo;
					}
					return $result;
				}
			)->once();

			$service = \mock(DT\Service::class, [$metadataProvider, $strategyManager, $validatorManager])->makePartial();
			$service->shouldReceive('getTypedDataStrategy')->with($to, $subset)->once()->andReturn($toStrategy);
			$service->shouldReceive('getTypedDataValidator')->with($to, $subset)->once()->andReturn($toValidator);

			/** @var DT\Service $service */
			$transferResult = $service->transferToTypedData($from, $to, $subset);
			\expect($transferResult)->toBe($violations);
			\expect($to)->toBe($updatedTo);
		});
		\it('returns violations found during destination extraction', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$toStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);

			$from = 1;
			$to = \mock();
			$subset = 'test';
			$extractedToError = new DT\Exception\InvalidData(['test' => 123]);
			$originalTo = $to;

			$toStrategy->shouldReceive('extract')->with($to)->once()->andThrow($extractedToError);

			$service = \mock(DT\Service::class, [$metadataProvider, $strategyManager, $validatorManager])->makePartial();
			$service->shouldReceive('getTypedDataStrategy')->with($to, $subset)->once()->andReturn($toStrategy);
			$service->shouldReceive('getTypedDataValidator')->with($to, $subset)->once()->andReturn($toValidator);

			/** @var DT\Service $service */
			$transferResult = $service->transferToTypedData($from, $to, $subset);
			\expect($transferResult)->toBe($extractedToError->getViolations());
			\expect($to)->toBe($originalTo);
		});
		\it('returns violations found during merge of source to extracted destination', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$toStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);

			$from = 1;
			$to = \mock();
			$subset = 'test';
			$extractedTo = 2;
			$updatedExtractedToError = new DT\Exception\InvalidData(['test' => 123]);
			$originalTo = $to;

			$toStrategy->shouldReceive('extract')->with($to)->once()->andReturn($extractedTo);
			$toStrategy->shouldReceive('merge')->once()->andThrow($updatedExtractedToError);

			$service = \mock(DT\Service::class, [$metadataProvider, $strategyManager, $validatorManager])->makePartial();
			$service->shouldReceive('getTypedDataStrategy')->with($to, $subset)->once()->andReturn($toStrategy);
			$service->shouldReceive('getTypedDataValidator')->with($to, $subset)->once()->andReturn($toValidator);

			/** @var DT\Service $service */
			$transferResult = $service->transferToTypedData($from, $to, $subset);
			\expect($transferResult)->toBe($updatedExtractedToError->getViolations());
			\expect($to)->toBe($originalTo);
		});
		\it('returns violations found during validation of updated extracted destination', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$toStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);

			$from = 1;
			$to = \mock();
			$subset = 'test';
			$extractedTo = 2;
			$updatedExtractedTo = 3;
			$violations = ['test' => 123];
			$originalTo = $to;

			$toStrategy->shouldReceive('extract')->with($to)->once()->andReturn($extractedTo);
			$toStrategy->shouldReceive('merge')->withArgs(
				function($a, &$b) use (&$from, &$extractedTo, &$updatedExtractedTo)
				{
					$result = ($a === $from) && ($b === $extractedTo);
					if($result)
					{
						$b = $updatedExtractedTo;
					}
					return $result;
				}
			)->once();
			$toValidator->shouldReceive('validate')->with($updatedExtractedTo)->once()->andReturn($violations);

			$service = \mock(DT\Service::class, [$metadataProvider, $strategyManager, $validatorManager])->makePartial();
			$service->shouldReceive('getTypedDataStrategy')->with($to, $subset)->once()->andReturn($toStrategy);
			$service->shouldReceive('getTypedDataValidator')->with($to, $subset)->once()->andReturn($toValidator);

			/** @var DT\Service $service */
			$transferResult = $service->transferToTypedData($from, $to, $subset);
			\expect($transferResult)->toBe($violations);
			\expect($to)->toBe($originalTo);
		});
		\it('returns violations found during hydration of extracted source to destination', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$toStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$toValidator = \mock(DT\Validator\ValidatorInterface::class);

			$from = 1;
			$to = \mock();
			$subset = 'test';
			$extractedTo = 2;
			$updatedExtractedTo = 3;
			$violations = [];
			$updatedToError = new DT\Exception\InvalidData(['test' => 123]);
			$originalTo = $to;

			$toStrategy->shouldReceive('extract')->with($to)->once()->andReturn($extractedTo);
			$toStrategy->shouldReceive('merge')->withArgs(
				function($a, &$b) use (&$from, &$extractedTo, &$updatedExtractedTo)
				{
					$result = ($a === $from) && ($b === $extractedTo);
					if($result)
					{
						$b = $updatedExtractedTo;
					}
					return $result;
				}
			)->once();
			$toValidator->shouldReceive('validate')->with($updatedExtractedTo)->once()->andReturn($violations);
			$toStrategy->shouldReceive('hydrate')->once()->andThrow($updatedToError);

			$service = \mock(DT\Service::class, [$metadataProvider, $strategyManager, $validatorManager])->makePartial();
			$service->shouldReceive('getTypedDataStrategy')->with($to, $subset)->once()->andReturn($toStrategy);
			$service->shouldReceive('getTypedDataValidator')->with($to, $subset)->once()->andReturn($toValidator);

			/** @var DT\Service $service */
			$transferResult = $service->transferToTypedData($from, $to, $subset);
			\expect($transferResult)->toBe($updatedToError->getViolations());
			\expect($to)->toBe($originalTo);
		});
	});
	\describe('->extractFromTypedData', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('extracts valid data', function ()
		{
			$metadataProvider = \mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = \mock(DT\Strategy\PluginManager::class);
			$validatorManager = \mock(DT\Validator\PluginManager::class);

			$fromStrategy = \mock(DT\Strategy\StrategyInterface::class);

			$from = \mock();
			$subset = 'test';
			$extractedFrom = 1;

			$fromStrategy->shouldReceive('extract')->with($from)->once()->andReturn($extractedFrom);
			$service = \mock(DT\Service::class, [$metadataProvider, $strategyManager, $validatorManager])->makePartial();
			$service->shouldReceive('getTypedDataStrategy')->with($from, $subset)->once()->andReturn($fromStrategy);

			/** @var DT\Service $service */
			$transferResult = $service->extractFromTypedData($from, $subset);
			\expect($transferResult)->toBe($extractedFrom);
		});
	});
});
