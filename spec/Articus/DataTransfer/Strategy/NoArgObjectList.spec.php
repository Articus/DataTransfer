<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;

\describe(DT\Strategy\NoArgObjectList::class, function ()
{
	\describe('->hydrate', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('hydrates from null', function ()
		{
			$source = null;
			$destination = 'test';
			$newDestination = $source;
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);
			$strategy->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('hydrates to destination', function ()
		{
			$sourceItem = \mock();
			$destination = null;
			$newDestinationItem = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typeStrategy->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$sourceItem, &$destination, &$newDestinationItem)
				{
					$result = ($a === $sourceItem) && ($b instanceof Example\DTO\Data);
					if ($result)
					{
						$b = $newDestinationItem;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);
			$strategy->hydrate([$sourceItem], $destination);
			\expect($destination)->toBe([$newDestinationItem]);
		});
		\it('throws on non list source', function ()
		{
			$source = \mock();
			$destination = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);
			$error = new \LogicException(\sprintf('Hydration can be done only from iterable list, not %s', \get_class($destination)));
			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($error);
		});
		\it('rethrows wrapped invalid data exception', function ()
		{
			$sourceItem = \mock();
			$destination = null;
			$violations = ['test' => 123];
			$innerError = new DT\Exception\InvalidData($violations);

			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typeStrategy->shouldReceive('hydrate')
				->with($sourceItem, \Mockery::type(Example\DTO\Data::class))
				->once()
				->andThrow($innerError)
			;
			$error = new DT\Exception\InvalidData([DT\Validator\Collection::INVALID_INNER => [$violations]], $innerError);

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);

			\expect(function () use (&$strategy, &$sourceItem, &$destination)
			{
				$strategy->hydrate([$sourceItem], $destination);
			})->toThrow($error);
		});
	});

	\describe('->merge', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('merges null', function ()
		{
			$source = null;
			$destination = 'test';
			$newDestination = $source;
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);
			$strategy->merge($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('merges into destination', function ()
		{
			$sourceItem = \mock();
			$defaultDestinationItem = \mock();
			$destination = null;
			$newDestinationItem = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typeStrategy->shouldReceive('extract')
				->with(\Mockery::type(Example\DTO\Data::class))
				->andReturn($defaultDestinationItem)
				->once()
			;
			$typeStrategy->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$sourceItem, &$defaultDestinationItem, &$newDestinationItem)
				{
					$result = ($a === $sourceItem) && ($b === $defaultDestinationItem);
					if ($result)
					{
						$b = $newDestinationItem;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);
			$strategy->merge([$sourceItem], $destination);
			\expect($destination)->toBe([$newDestinationItem]);
		});
		\it('throws on non list source', function ()
		{
			$source = \mock();
			$destination = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);
			$error = new \LogicException(\sprintf('Merge can be done only for iterable list, not %s', \get_class($destination)));
			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->merge($source, $destination);
			})->toThrow($error);
		});
		\it('rethrows wrapped invalid data exception', function ()
		{
			$sourceItem = \mock();
			$defaultDestinationItem = \mock();
			$destination = null;
			$violations = ['test' => 123];
			$innerError = new DT\Exception\InvalidData($violations);

			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typeStrategy->shouldReceive('extract')
				->with(\Mockery::type(Example\DTO\Data::class))
				->andReturn($defaultDestinationItem)
				->once()
			;
			$typeStrategy->shouldReceive('merge')
				->with($sourceItem, $defaultDestinationItem)
				->once()
				->andThrow($innerError)
			;
			$error = new DT\Exception\InvalidData([DT\Validator\Collection::INVALID_INNER => [$violations]], $innerError);

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);

			\expect(function () use (&$strategy, &$sourceItem, &$destination)
			{
				$strategy->merge([$sourceItem], $destination);
			})->toThrow($error);
		});
	});

});
