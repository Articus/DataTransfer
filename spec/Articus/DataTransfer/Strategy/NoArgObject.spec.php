<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;

\describe(DT\Strategy\NoArgObject::class, function ()
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

			$strategy = new DT\Strategy\NoArgObject($typeStrategy, Example\DTO\Data::class);
			$strategy->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('hydrates to null', function ()
		{
			$source = \mock();
			$destination = null;
			$newDestination = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typeStrategy->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$source, &$destination, &$newDestination)
				{
					$result = ($a === $source) && ($b instanceof Example\DTO\Data);
					if ($result)
					{
						$b = $newDestination;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\NoArgObject($typeStrategy, Example\DTO\Data::class);
			$strategy->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('hydrates to object of valid type', function ()
		{
			$source = \mock();
			$destination = \mock(Example\DTO\Data::class);
			$newDestination = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typeStrategy->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$source, &$destination, &$newDestination)
				{
					$result = ($a === $source) && ($b === $destination);
					if ($result)
					{
						$b = $newDestination;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\NoArgObject($typeStrategy, Example\DTO\Data::class);
			$strategy->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('throws on destination of invalid type', function ()
		{
			$source = \mock();
			$destination = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);

			$strategy = new DT\Strategy\NoArgObject($typeStrategy, Example\DTO\Data::class);
			$error = new \LogicException(\sprintf('Hydration can be done only to %s, not %s', Example\DTO\Data::class, \get_class($destination)));
			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
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

			$strategy = new DT\Strategy\NoArgObject($typeStrategy, Example\DTO\Data::class);
			$strategy->merge($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('merges into null', function ()
		{
			$source = \mock();
			$destination = null;
			$defaultDestination = \mock();
			$newDestination = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typeStrategy->shouldReceive('extract')
				->with(\Mockery::type(Example\DTO\Data::class))
				->andReturn($defaultDestination)
				->once()
			;
			$typeStrategy->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$source, &$defaultDestination, &$newDestination)
				{
					$result = ($a === $source) && ($b === $defaultDestination);
					if ($result)
					{
						$b = $newDestination;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\NoArgObject($typeStrategy, Example\DTO\Data::class);
			$strategy->merge($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('merges source into destination', function ()
		{
			$source = \mock();
			$destination = \mock();
			$newDestination = \mock();
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typeStrategy->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$source, &$destination, &$newDestination)
				{
					$result = ($a === $source) && ($b === $destination);
					if ($result)
					{
						$b = $newDestination;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\NoArgObject($typeStrategy, Example\DTO\Data::class);
			$strategy->merge($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
	});
});
