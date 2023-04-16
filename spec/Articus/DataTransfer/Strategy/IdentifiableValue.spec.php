<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use spec\Example;

describe(DT\Strategy\IdentifiableValue::class, function ()
{
	describe('->hydrate', function ()
	{
		afterEach(function ()
		{
			Mockery::close();
		});
		it('hydrates from null', function ()
		{
			$source = null;
			$destination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = mock(Example\InvokableInterface::class);
			$typedValueConstructor = mock(Example\InvokableInterface::class);
			$untypedValueConstructor = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\IdentifiableValue(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				$typedValueConstructor,
				$untypedValueConstructor
			);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBeNull();
		});
		it('resets destination and delegates hydration to value strategy if destination is null', function()
		{
			$source = mock();
			$destination = null;
			$defaultDestination = mock();
			$newDestination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = mock(Example\InvokableInterface::class);
			$typedValueConstructor = mock(Example\InvokableInterface::class);
			$untypedValueConstructor = mock(Example\InvokableInterface::class);

			$typedValueConstructor->shouldReceive('__invoke')->with($source)->andReturn($defaultDestination)->once();
			$valueStrategy->shouldReceive('hydrate')->withArgs(
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

			$strategy = new DT\Strategy\IdentifiableValue(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				$typedValueConstructor,
				$untypedValueConstructor
			);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBe($newDestination);
		});
		it('resets destination and delegates hydration to value strategy if source and destination have distinct identity', function()
		{
			$source = mock();
			$destination = mock();
			$sourceId = 'test1';
			$destinationId = 'test2';
			$defaultDestination = mock();
			$newDestination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = mock(Example\InvokableInterface::class);
			$typedValueConstructor = mock(Example\InvokableInterface::class);
			$untypedValueConstructor = mock(Example\InvokableInterface::class);

			$typedValueIdentifier->shouldReceive('__invoke')->with($destination)->andReturn($destinationId)->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($source)->andReturn($sourceId)->once();
			$typedValueConstructor->shouldReceive('__invoke')->with($source)->andReturn($defaultDestination)->once();
			$valueStrategy->shouldReceive('hydrate')->withArgs(
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

			$strategy = new DT\Strategy\IdentifiableValue(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				$typedValueConstructor,
				$untypedValueConstructor
			);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBe($newDestination);
		});
		it('keeps destination and delegates hydration to value strategy if source and destination have same identity', function()
		{
			$source = mock();
			$destination = mock();
			$sourceId = 'test';
			$destinationId = 'test';
			$newDestination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = mock(Example\InvokableInterface::class);
			$typedValueConstructor = mock(Example\InvokableInterface::class);
			$untypedValueConstructor = mock(Example\InvokableInterface::class);

			$typedValueIdentifier->shouldReceive('__invoke')->with($destination)->andReturn($destinationId)->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($source)->andReturn($sourceId)->once();
			$valueStrategy->shouldReceive('hydrate')->withArgs(
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

			$strategy = new DT\Strategy\IdentifiableValue(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				$typedValueConstructor,
				$untypedValueConstructor
			);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBe($newDestination);
		});
	});
	describe('->merge', function ()
	{
		afterEach(function ()
		{
			Mockery::close();
		});
		it('merges from null', function ()
		{
			$source = null;
			$destination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = mock(Example\InvokableInterface::class);
			$typedValueConstructor = mock(Example\InvokableInterface::class);
			$untypedValueConstructor = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\IdentifiableValue(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				$typedValueConstructor,
				$untypedValueConstructor
			);
			$strategy->merge($source, $destination);
			expect($destination)->toBeNull();
		});
		it('resets destination and delegates merge to value strategy if destination is null', function()
		{
			$source = mock();
			$destination = null;
			$defaultDestination = mock();
			$newDestination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = mock(Example\InvokableInterface::class);
			$typedValueConstructor = mock(Example\InvokableInterface::class);
			$untypedValueConstructor = mock(Example\InvokableInterface::class);

			$untypedValueConstructor->shouldReceive('__invoke')->with($source)->andReturn($defaultDestination)->once();
			$valueStrategy->shouldReceive('merge')->withArgs(
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

			$strategy = new DT\Strategy\IdentifiableValue(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				$typedValueConstructor,
				$untypedValueConstructor
			);
			$strategy->merge($source, $destination);
			expect($destination)->toBe($newDestination);
		});
		it('resets destination and delegates merge to value strategy if source and destination have distinct identity', function()
		{
			$source = mock();
			$destination = mock();
			$sourceId = 'test1';
			$destinationId = 'test2';
			$defaultDestination = mock();
			$newDestination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = mock(Example\InvokableInterface::class);
			$typedValueConstructor = mock(Example\InvokableInterface::class);
			$untypedValueConstructor = mock(Example\InvokableInterface::class);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($source)->andReturn($sourceId)->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($destination)->andReturn($destinationId)->once();
			$untypedValueConstructor->shouldReceive('__invoke')->with($source)->andReturn($defaultDestination)->once();
			$valueStrategy->shouldReceive('merge')->withArgs(
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

			$strategy = new DT\Strategy\IdentifiableValue(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				$typedValueConstructor,
				$untypedValueConstructor
			);
			$strategy->merge($source, $destination);
			expect($destination)->toBe($newDestination);
		});
		it('keeps destination and delegates merge to value strategy if source and destination have same identity', function()
		{
			$source = mock();
			$destination = mock();
			$sourceId = 'test';
			$destinationId = 'test';
			$newDestination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = mock(Example\InvokableInterface::class);
			$typedValueConstructor = mock(Example\InvokableInterface::class);
			$untypedValueConstructor = mock(Example\InvokableInterface::class);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($source)->andReturn($sourceId)->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($destination)->andReturn($destinationId)->once();
			$valueStrategy->shouldReceive('merge')->withArgs(
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

			$strategy = new DT\Strategy\IdentifiableValue(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				$typedValueConstructor,
				$untypedValueConstructor
			);
			$strategy->merge($source, $destination);
			expect($destination)->toBe($newDestination);
		});
	});
});
