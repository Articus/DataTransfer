<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use spec\Example;

describe(DT\Strategy\SerializableValue::class, function ()
{
	describe('->extract', function ()
	{
		afterEach(function ()
		{
			Mockery::close();
		});
		it('extracts null', function ()
		{
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			expect($strategy->extract(null))->toBeNull();
		});
		it('delegates non-null extraction to value strategy', function ()
		{
			$source = mock();
			$extractedSource = mock();
			$destination = 'some string';
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$valueStrategy->shouldReceive('extract')->with($source)->andReturn($extractedSource)->once();
			$serializer->shouldReceive('__invoke')->with($extractedSource)->andReturn($destination)->once();

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			expect($strategy->extract($source))->toBe($destination);
		});
	});
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
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBeNull();
		});
		it('throws on non-string source', function ()
		{
			$source = mock();
			$destination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			try
			{
				$strategy->hydrate($source, $destination);
				throw new LogicException('No expected exception');
			}
			catch (DT\Exception\InvalidData $e)
			{
				expect($e->getViolations())->toBe(DT\Exception\InvalidData::DEFAULT_VIOLATION);
				expect($e->getPrevious())->toBeAnInstanceOf(InvalidArgumentException::class);
				expect($e->getPrevious()->getMessage())->toBe(
					sprintf('Hydration can be done only from string, not %s', get_class($source))
				);
			}
		});
		it('delegates hydration to value strategy if source is string', function ()
		{
			$source = 'aaa';
			$extractedSource = mock();
			$oldDestination = mock();
			$newDestination = mock();
			$destination = $oldDestination;
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$unserializer->shouldReceive('__invoke')->with($source)->andReturn($extractedSource)->once();
			$valueStrategy->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$extractedSource, &$oldDestination, &$newDestination)
				{
					$result = ($a === $extractedSource) && ($b === $oldDestination);
					if ($result)
					{
						$b = $newDestination;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
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
			$destination = 'some destination';
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			$strategy->merge($source, $destination);
			expect($destination)->toBeNull();
		});
		it('throws on non-string source', function ()
		{
			$source = mock();
			$destination = 'some destination';
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			try
			{
				$strategy->merge($source, $destination);
				throw new LogicException('No expected exception');
			}
			catch (DT\Exception\InvalidData $e)
			{
				expect($e->getViolations())->toBe(DT\Exception\InvalidData::DEFAULT_VIOLATION);
				expect($e->getPrevious())->toBeAnInstanceOf(InvalidArgumentException::class);
				expect($e->getPrevious()->getMessage())->toBe(
					sprintf('Merge can be done only from string, not %s', get_class($source))
				);
			}
		});
		it('copies source to destination if source is string and destination is null', function ()
		{
			$source = 'some source';
			$destination = null;
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			$strategy->merge($source, $destination);
			expect($destination)->toBe($source);
		});
		it('throws on string source and non-string destination', function ()
		{
			$source = 'some source';
			$destination = mock();
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			try
			{
				$strategy->merge($source, $destination);
				throw new LogicException('No expected exception');
			}
			catch (DT\Exception\InvalidData $e)
			{
				expect($e->getViolations())->toBe(DT\Exception\InvalidData::DEFAULT_VIOLATION);
				expect($e->getPrevious())->toBeAnInstanceOf(InvalidArgumentException::class);
				expect($e->getPrevious()->getMessage())->toBe(
					sprintf('Merge can be done only to string, not %s', get_class($destination))
				);
			}
		});
		it('delegates merge to value strategy if source is string and destination is string', function ()
		{
			$source = 'some source';
			$extractedSource = mock();
			$oldDestination = 'some old destination';
			$oldExtractedDestination = mock();
			$newDestination = 'some new destination';
			$newExtractedDestination = mock();
			$destination = $oldDestination;
			$valueStrategy = mock(DT\Strategy\StrategyInterface::class);
			$serializer = mock(Example\InvokableInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$unserializer->shouldReceive('__invoke')->with($source)->andReturn($extractedSource)->once();
			$unserializer->shouldReceive('__invoke')->with($oldDestination)->andReturn($oldExtractedDestination)->once();
			$valueStrategy->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$extractedSource, &$oldExtractedDestination, &$newExtractedDestination)
				{
					$result = ($a === $extractedSource) && ($b === $oldExtractedDestination);
					if ($result)
					{
						$b = $newExtractedDestination;
					}
					return $result;
				}
			)->once();
			$serializer->shouldReceive('__invoke')->with($newExtractedDestination)->andReturn($newDestination)->once();

			$strategy = new DT\Strategy\SerializableValue($valueStrategy, $serializer, $unserializer);
			$strategy->merge($source, $destination);
			expect($destination)->toBe($newDestination);
		});
	});
});
