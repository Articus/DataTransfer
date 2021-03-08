<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;

\describe(DT\Strategy\IdentifiableValueList::class, function ()
{
	\describe('->hydrate', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('throws on non iterable source', function ()
		{
			$source = \mock();
			$destination = null;
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException(\sprintf('Hydration can be done only from iterable, not %s', \get_class($source)));

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($exception);
		});
		\it('throws on non iterable destination', function ()
		{
			$source = [];
			$destination = \mock();
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException(\sprintf('Hydration can be done only to iterable, not %s', \get_class($destination)));

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($exception);
		});
		\it('throws if source have items with same identifier', function ()
		{
			$source = [\mock(), \mock()];
			$destination = [];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new DT\Exception\InvalidData([
				DT\Validator\Collection::INVALID_INNER => [
					1 => [DT\Strategy\IdentifiableValueList::DUPLICATE_ID => 'Same identifier as item 0']]
				]
			);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('test123')->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('test123')->once();

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			try
			{
				$strategy->hydrate($source, $destination);
				throw new \LogicException('No exception');
			}
			catch (DT\Exception\InvalidData $e)
			{
				\expect($e->getViolations())->toBe($exception->getViolations());
			}
		});
		\it('throws if destination have items with same identifier', function ()
		{
			$source = [];
			$destination = [\mock(), \mock()];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException('List contains items with same identifier');

			$typedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturn('test123')->once();
			$typedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturn('test123')->once();

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($exception);
		});
		\it('wraps and rethrows invalid data exceptions from value strategy', function ()
		{
			$source = [\mock()];
			$destination = [\mock()];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$innerException = new DT\Exception\InvalidData(['test' => 123]);
			$exception = new DT\Exception\InvalidData(
				[DT\Validator\Collection::INVALID_INNER => [0 => $innerException->getViolations()]],
				$innerException
			);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('test123')->once();
			$typedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturn('test123')->once();
			$valueStrategy->shouldReceive('hydrate')->with($source[0], $destination[0])->andThrow($innerException);

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			try
			{
				$strategy->hydrate($source, $destination);
				throw new \LogicException('No exception');
			}
			catch (DT\Exception\InvalidData $e)
			{
				\expect($e->getViolations())->toBe($exception->getViolations());
				\expect($e->getPrevious())->toBe($innerException);
			}
		});
		\it('hydrates identified source item to destination item with same identifier using value strategy', function ()
		{
			$sourceItem = \mock();
			$source = [&$sourceItem];
			$destinationItem = \mock();
			$destination = [&$destinationItem];
			$newDestinationItem = \mock();
			$newDestination = [&$newDestinationItem];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
			$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test123')->once();
			$valueStrategy->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$sourceItem, &$destinationItem, &$newDestinationItem)
				{
					$result = (($a === $sourceItem) && ($b === $destinationItem));
					if ($result)
					{
						$b = $newDestinationItem;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			$strategy->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\context('if there is typed value adder', function ()
		{
			\it('adds new destination item and hydrates identified source item to it using value strategy if there is no destination item with same identifier', function ()
			{
				$sourceItem = \mock();
				$source = [&$sourceItem];
				$defaultDestinationItem = \mock();
				$newDestinationItem = \mock();
				$destination = [];
				$newDestination = [&$newDestinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = function &(&$list, $untypedItem) use (&$destination, &$sourceItem, &$defaultDestinationItem)
				{
					if (($destination !== $list) || ($sourceItem !== $untypedItem))
					{
						throw new \InvalidArgumentException('Unexpected arguments for adder');
					}
					$list[] = &$defaultDestinationItem;
					return $defaultDestinationItem;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$sourceItem, &$defaultDestinationItem, &$newDestinationItem)
					{
						$result = (($a === $sourceItem) && ($b === $defaultDestinationItem));
						if ($result)
						{
							$b = $newDestinationItem;
						}
						return $result;
					}
				);

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					null,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('adds new destination item and hydrates unidentified source item to it using value strategy', function ()
			{
				$sourceItem = \mock();
				$source = [&$sourceItem];
				$defaultDestinationItem = \mock();
				$newDestinationItem = \mock();
				$destination = [];
				$newDestination = [&$newDestinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = function &(&$list, $untypedItem) use (&$destination, &$sourceItem, &$defaultDestinationItem)
				{
					if (($destination !== $list) || ($sourceItem !== $untypedItem))
					{
						throw new \InvalidArgumentException('Unexpected arguments for adder');
					}
					$list[] = &$defaultDestinationItem;
					return $defaultDestinationItem;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturnNull()->once();
				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$sourceItem, &$defaultDestinationItem, &$newDestinationItem)
					{
						$result = (($a === $sourceItem) && ($b === $defaultDestinationItem));
						if ($result)
						{
							$b = $newDestinationItem;
						}
						return $result;
					}
				);

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					null,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is no typed value adder', function ()
		{
			\it('ignores identified source item if there is no destination item with same identifier', function ()
			{
				$source = [\mock()];
				$destination = [];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('test123')->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('ignores unidentified source item', function ()
			{
				$source = [\mock()];
				$destination = [];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturnNull()->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is typed value remover', function ()
		{
			\it('removes identified destination item if there is no source item with same identifier', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = [&$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = function (&$list, $typedItem) use (&$destination, &$destinationItem)
				{
					if (($list !== $destination) || ($typedItem !== $destinationItem))
					{
						throw new \InvalidArgumentException('Unexpected arguments for remover');
					}
					unset($list[0]);
				};

				$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test123')->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('removes unidentified destination item', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = [&$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = function (&$list, $typedItem) use (&$destination, &$destinationItem)
				{
					if (($list !== $destination) || ($typedItem !== $destinationItem))
					{
						throw new \InvalidArgumentException('Unexpected arguments for remover');
					}
					unset($list[0]);
				};

				$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturnNull()->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is no typed value remover', function ()
		{
			\it('keeps identified destination item if there is no source item with same identifier', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = [&$destinationItem];
				$newDestination = [&$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);

				$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test123')->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('keeps unidentified destination item', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = [&$destinationItem];
				$newDestination = [&$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);

				$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturnNull()->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});

		\context('complex positive scenarios', function ()
		{
			\it('hydrates array with several scalars', function ()
			{
				$source = [1, 2, 3, 4, 5, 6];
				$destination = [10, 20, 30, 40, 50, 60];
				$defaults = [
					2 => 100,
					3 => 200,
					4 => 300,
					5 => 400,
				];
				$newDestination = [
					4 => 1000,
					5 => 2000,
					6 => 3000,
					7 => 4000,
					8 => 5000,
					9 => 6000,
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = function &(&$list, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$index = \array_search($untypedItem, $source, true);
					$list[] = &$defaults[$index];
					return $defaults[$index];
				};
				$typedValueRemover = function (&$list, $typedItem) use (&$destination)
				{
					$index = \array_search($typedItem, $destination, true);
					unset($list[$index]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[2])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[3])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[4])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[5])->andReturnNull()->once();

				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturnNull()->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturnNull()->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[2])->andReturn('id22')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[3])->andReturn('id12')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[4])->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[5])->andReturn('id1')->once();

				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$sourceIndex = \array_search($a, $source, true);
						$destinationIndex = \array_search($b, $destination, true);
						$result = ($sourceIndex !== false) && ($destinationIndex !== false);
						if ($result)
						{
							$b = $newDestination[$destinationIndex];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					$typedValueRemover,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('hydrates array with several objects', function ()
			{
				$source = [\mock(), \mock(), \mock(), \mock(), \mock(), \mock()];
				$destination = [\mock(), \mock(), \mock(), \mock(), \mock(), \mock()];
				$defaults = [
					2 => \mock(),
					3 => \mock(),
					4 => \mock(),
					5 => \mock(),
				];
				$newDestination = [
					4 => \mock(),
					5 => \mock(),
					6 => \mock(),
					7 => \mock(),
					8 => \mock(),
					9 => \mock(),
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = function &(&$list, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$index = \array_search($untypedItem, $source, true);
					$list[] = &$defaults[$index];
					return $defaults[$index];
				};
				$typedValueRemover = function (&$list, $typedItem) use (&$destination)
				{
					$index = \array_search($typedItem, $destination, true);
					unset($list[$index]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[2])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[3])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[4])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[5])->andReturnNull()->once();

				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturnNull()->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturnNull()->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[2])->andReturn('id22')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[3])->andReturn('id12')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[4])->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[5])->andReturn('id1')->once();

				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$sourceIndex = \array_search($a, $source, true);
						$destinationIndex = \array_search($b, $destination, true);
						$result = ($sourceIndex !== false) && ($destinationIndex !== false);
						if ($result)
						{
							$b = $newDestination[$destinationIndex];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					$typedValueRemover,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('hydrates array object with several scalars', function ()
			{
				$source = new \ArrayObject([1, 2, 3, 4, 5, 6]);
				$destination = new \ArrayObject([10, 20, 30, 40, 50, 60]);
				$defaults = [
					2 => 100,
					3 => 200,
					4 => 300,
					5 => 400,
				];
				$newDestination = [
					4 => 1000,
					5 => 2000,
					6 => 3000,
					7 => 4000,
					8 => 5000,
					9 => 6000,
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = function &(\ArrayObject &$list, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$index = \array_search($untypedItem, $source->getArrayCopy(), true);
					//Simple $list[] = &$defaults[$index] causes PHP error for ArrayObject
					$list[\max(\array_keys($list->getArrayCopy())) + 1] = &$defaults[$index];
					return $defaults[$index];
				};
				$typedValueRemover = function (\ArrayObject &$list, $typedItem) use (&$destination)
				{
					$index = \array_search($typedItem, $destination->getArrayCopy(), true);
					unset($list[$index]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[2])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[3])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[4])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[5])->andReturnNull()->once();

				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturnNull()->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturnNull()->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[2])->andReturn('id22')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[3])->andReturn('id12')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[4])->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[5])->andReturn('id1')->once();

				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$sourceIndex = \array_search($a, $source->getArrayCopy(), true);
						$destinationIndex = \array_search($b, $destination->getArrayCopy(), true);
						$result = ($sourceIndex !== false) && ($destinationIndex !== false);
						if ($result)
						{
							$b = $newDestination[$destinationIndex];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					$typedValueRemover,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination->getArrayCopy())->toBe($newDestination);
			});
			\it('hydrates array object with several objects', function ()
			{
				$source = new \ArrayObject([\mock(), \mock(), \mock(), \mock(), \mock(), \mock()]);
				$destination = new \ArrayObject([\mock(), \mock(), \mock(), \mock(), \mock(), \mock()]);
				$defaults = [
					2 => \mock(),
					3 => \mock(),
					4 => \mock(),
					5 => \mock(),
				];
				$newDestination = [
					4 => \mock(),
					5 => \mock(),
					6 => \mock(),
					7 => \mock(),
					8 => \mock(),
					9 => \mock(),
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = function &(\ArrayObject &$list, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$index = \array_search($untypedItem, $source->getArrayCopy(), true);
					//Simple $list[] = &$defaults[$index] causes PHP error for ArrayObject
					$list[\max(\array_keys($list->getArrayCopy())) + 1] = &$defaults[$index];
					return $defaults[$index];
				};
				$typedValueRemover = function (\ArrayObject &$list, $typedItem) use (&$destination)
				{
					$index = \array_search($typedItem, $destination->getArrayCopy(), true);
					unset($list[$index]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[2])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[3])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[4])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[5])->andReturnNull()->once();

				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturnNull()->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturnNull()->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[2])->andReturn('id22')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[3])->andReturn('id12')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[4])->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination[5])->andReturn('id1')->once();

				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$sourceIndex = \array_search($a, $source->getArrayCopy(), true);
						$destinationIndex = \array_search($b, $destination->getArrayCopy(), true);
						$result = ($sourceIndex !== false) && ($destinationIndex !== false);
						if ($result)
						{
							$b = $newDestination[$destinationIndex];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					$typedValueRemover,
					null
				);
				$strategy->hydrate($source, $destination);
				\expect($destination->getArrayCopy())->toBe($newDestination);
			});
		});
	});
	\describe('->merge', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('throws on non iterable source', function ()
		{
			$source = \mock();
			$destination = null;
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException(\sprintf('Merge can be done only from iterable, not %s', \get_class($source)));

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->merge($source, $destination);
			})->toThrow($exception);
		});
		\it('throws on non iterable destination', function ()
		{
			$source = [];
			$destination = \mock();
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException(\sprintf('Merge can be done only to iterable, not %s', \get_class($destination)));

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->merge($source, $destination);
			})->toThrow($exception);
		});
		\it('throws if source have items with same identifier', function ()
		{
			$source = [\mock(), \mock()];
			$destination = [];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new DT\Exception\InvalidData([
					DT\Validator\Collection::INVALID_INNER => [
						1 => [DT\Strategy\IdentifiableValueList::DUPLICATE_ID => 'Same identifier as item 0']]
				]
			);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('test123')->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('test123')->once();

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			try
			{
				$strategy->merge($source, $destination);
				throw new \LogicException('No exception');
			}
			catch (DT\Exception\InvalidData $e)
			{
				\expect($e->getViolations())->toBe($exception->getViolations());
			}
		});
		\it('throws if destination have items with same identifier', function ()
		{
			$source = [];
			$destination = [\mock(), \mock()];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException('List contains items with same identifier');

			$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturn('test123')->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturn('test123')->once();

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->merge($source, $destination);
			})->toThrow($exception);
		});
		\it('wraps and rethrows invalid data exceptions from value strategy', function ()
		{
			$source = [\mock()];
			$destination = [\mock()];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$innerException = new DT\Exception\InvalidData(['test' => 123]);
			$exception = new DT\Exception\InvalidData(
				[DT\Validator\Collection::INVALID_INNER => [0 => $innerException->getViolations()]],
				$innerException
			);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('test123')->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturn('test123')->once();
			$valueStrategy->shouldReceive('merge')->with($source[0], $destination[0])->andThrow($innerException);

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			try
			{
				$strategy->merge($source, $destination);
				throw new \LogicException('No exception');
			}
			catch (DT\Exception\InvalidData $e)
			{
				\expect($e->getViolations())->toBe($exception->getViolations());
				\expect($e->getPrevious())->toBe($innerException);
			}
		});
		\it('merges identified source item to destination item with same identifier using value strategy', function ()
		{
			$sourceItem = \mock();
			$source = [&$sourceItem];
			$destinationItem = \mock();
			$destination = [&$destinationItem];
			$newDestinationItem = \mock();
			$newDestination = [&$newDestinationItem];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test123')->once();
			$valueStrategy->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$sourceItem, &$destinationItem, &$newDestinationItem)
				{
					$result = (($a === $sourceItem) && ($b === $destinationItem));
					if ($result)
					{
						$b = $newDestinationItem;
					}
					return $result;
				}
			)->once();

			$strategy = new DT\Strategy\IdentifiableValueList(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null
			);

			$strategy->merge($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\context('if there is typed value adder', function ()
		{
			\context('if there is untyped value constructor', function ()
			{
				\it('adds new destination item and merges identified source item to it using value strategy if there is no destination item with same identifier', function ()
				{
					$sourceItem = \mock();
					$source = [&$sourceItem];
					$defaultDestinationItem = \mock();
					$newDestinationItem = \mock();
					$destination = [];
					$newDestination = [&$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
					$typedValueAdder = \mock(Example\InvokableInterface::class);
					$untypedValueConstructor = \mock(Example\InvokableInterface::class);

					$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
					$untypedValueConstructor->shouldReceive('__invoke')->with($sourceItem)->andReturn($defaultDestinationItem)->once();
					$valueStrategy->shouldReceive('merge')->withArgs(
						function ($a, &$b) use (&$sourceItem, &$defaultDestinationItem, &$newDestinationItem)
						{
							$result = (($a === $sourceItem) && ($b === $defaultDestinationItem));
							if ($result)
							{
								$b = $newDestinationItem;
							}
							return $result;
						}
					);

					$strategy = new DT\Strategy\IdentifiableValueList(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueAdder,
						null,
						$untypedValueConstructor
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
				\it('adds new destination item and merges unidentified source item to it using value strategy', function ()
				{
					$sourceItem = \mock();
					$source = [&$sourceItem];
					$defaultDestinationItem = \mock();
					$newDestinationItem = \mock();
					$destination = [];
					$newDestination = [&$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
					$typedValueAdder = \mock(Example\InvokableInterface::class);
					$untypedValueConstructor = \mock(Example\InvokableInterface::class);

					$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturnNull()->once();
					$untypedValueConstructor->shouldReceive('__invoke')->with($sourceItem)->andReturn($defaultDestinationItem)->once();
					$valueStrategy->shouldReceive('merge')->withArgs(
						function ($a, &$b) use (&$sourceItem, &$defaultDestinationItem, &$newDestinationItem)
						{
							$result = (($a === $sourceItem) && ($b === $defaultDestinationItem));
							if ($result)
							{
								$b = $newDestinationItem;
							}
							return $result;
						}
					);

					$strategy = new DT\Strategy\IdentifiableValueList(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueAdder,
						null,
						$untypedValueConstructor
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
			});
			\context('if there is no untyped value constructor', function ()
			{
				\it('adds new destination item and hydrates identified source item to it using value strategy if there is no destination item with same identifier', function ()
				{
					$sourceItem = \mock();
					$source = [&$sourceItem];
					$newDestinationItem = \mock();
					$destination = [];
					$newDestination = [&$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier	= \mock(Example\InvokableInterface::class);
					$typedValueAdder = \mock(Example\InvokableInterface::class);

					$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
					$valueStrategy->shouldReceive('merge')->withArgs(
						function ($a, &$b) use (&$sourceItem, &$newDestinationItem)
						{
							$result = (($a === $sourceItem) && ($b === null));
							if ($result)
							{
								$b = $newDestinationItem;
							}
							return $result;
						}
					);

					$strategy = new DT\Strategy\IdentifiableValueList(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueAdder,
						null,
						null
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
				\it('adds new destination item and hydrates unidentified source item to it using value strategy', function ()
				{
					$sourceItem = \mock();
					$source = [&$sourceItem];
					$newDestinationItem = \mock();
					$destination = [];
					$newDestination = [&$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueAdder = \mock(Example\InvokableInterface::class);

					$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturnNull()->once();
					$valueStrategy->shouldReceive('merge')->withArgs(
						function ($a, &$b) use (&$sourceItem, &$newDestinationItem)
						{
							$result = (($a === $sourceItem) && ($b === null));
							if ($result)
							{
								$b = $newDestinationItem;
							}
							return $result;
						}
					);

					$strategy = new DT\Strategy\IdentifiableValueList(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueAdder,
						null,
						null
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
			});
		});
		\context('if there is no typed value adder', function ()
		{
			\it('ignores identified source item if there is no destination item with same identifier', function ()
			{
				$source = [\mock()];
				$destination = [];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('test123')->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('ignores unidentified source item', function ()
			{
				$source = [\mock()];
				$destination = [];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturnNull()->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is typed value remover', function ()
		{
			\it('removes identified destination item if there is no source item with same identifier', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = [&$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test123')->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('removes unidentified destination item', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = [&$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturnNull()->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is no typed value remover', function ()
		{
			\it('keeps identified destination item if there is no source item with same identifier', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = [&$destinationItem];
				$newDestination = [&$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test123')->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('keeps unidentified destination item', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = [&$destinationItem];
				$newDestination = [&$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturnNull()->once();

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});

		\context('complex positive scenarios', function ()
		{
			\it('merges array with several scalars', function ()
			{
				$source = [1, 2, 3, 4, 5, 6];
				$destination = [10, 20, 30, 40, 50, 60];
				$defaults = [
					2 => 100,
					3 => 200,
					4 => 300,
					5 => 400,
				];
				$newDestination = [
					1000,
					2000,
					3000,
					4000,
					5000,
					6000,
					$destination[2],
					$destination[3],
					$destination[0],
					$destination[1]
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($untypedItem) use (&$source, &$defaults)
				{
					$sourceIndex = \array_search($untypedItem, $source, true);
					return $defaults[$sourceIndex];
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[2])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[3])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[4])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[5])->andReturnNull()->once();

				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[2])->andReturn('id22')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[3])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[4])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[5])->andReturn('id1')->once();

				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$sourceIndex = \array_search($a, $source, true);
						$destinationIndex = \array_search($b, $destination, true);
						$result = ($sourceIndex !== false) && ($destinationIndex !== false);
						if ($result)
						{
							$b = $newDestination[$destinationIndex];
						}
						return $result;
					}
				)->times(\count($source));

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					null,
					$untypedValueConstructor
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('hydrates array with several objects', function ()
			{
				$source = [\mock(), \mock(), \mock(), \mock(), \mock(), \mock()];
				$destination = [\mock(), \mock(), \mock(), \mock(), \mock(), \mock()];
				$defaults = [
					2 => \mock(),
					3 => \mock(),
					4 => \mock(),
					5 => \mock(),
				];
				$newDestination = [
					\mock(),
					\mock(),
					\mock(),
					\mock(),
					\mock(),
					\mock(),
					$destination[2],
					$destination[3],
					$destination[0],
					$destination[1]
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($untypedItem) use (&$source, &$defaults)
				{
					$sourceIndex = \array_search($untypedItem, $source, true);
					return $defaults[$sourceIndex];
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[2])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[3])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[4])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[5])->andReturnNull()->once();

				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[2])->andReturn('id22')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[3])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[4])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[5])->andReturn('id1')->once();

				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$sourceIndex = \array_search($a, $source, true);
						$destinationIndex = \array_search($b, $destination, true);
						$result = ($sourceIndex !== false) && ($destinationIndex !== false);
						if ($result)
						{
							$b = $newDestination[$destinationIndex];
						}
						return $result;
					}
				)->times(\count($source));

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					null,
					$untypedValueConstructor
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('hydrates array object with several scalars', function ()
			{
				$source = new \ArrayObject([1, 2, 3, 4, 5, 6]);
				$destination = new \ArrayObject([10, 20, 30, 40, 50, 60]);
				$defaults = [
					2 => 100,
					3 => 200,
					4 => 300,
					5 => 400,
				];
				$newDestination = [
					1000,
					2000,
					3000,
					4000,
					5000,
					6000,
					$destination[2],
					$destination[3],
					$destination[0],
					$destination[1]
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($untypedItem) use (&$source, &$defaults)
				{
					$sourceIndex = \array_search($untypedItem, $source->getArrayCopy(), true);
					return $defaults[$sourceIndex];
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[2])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[3])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[4])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[5])->andReturnNull()->once();

				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[2])->andReturn('id22')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[3])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[4])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[5])->andReturn('id1')->once();

				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$sourceIndex = \array_search($a, $source->getArrayCopy(), true);
						$destinationIndex = is_array($destination) ? \array_search($b, $destination, true) : false;
						$result = ($sourceIndex !== false) && ($destinationIndex !== false);
						if ($result)
						{
							$b = $newDestination[$destinationIndex];
						}
						return $result;
					}
				)->times(\count($source));

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					null,
					$untypedValueConstructor
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('hydrates array object with several objects', function ()
			{
				$source = new \ArrayObject([\mock(), \mock(), \mock(), \mock(), \mock(), \mock()]);
				$destination = new \ArrayObject([\mock(), \mock(), \mock(), \mock(), \mock(), \mock()]);
				$defaults = [
					2 => \mock(),
					3 => \mock(),
					4 => \mock(),
					5 => \mock(),
				];
				$newDestination = [
					\mock(),
					\mock(),
					\mock(),
					\mock(),
					\mock(),
					\mock(),
					$destination[2],
					$destination[3],
					$destination[0],
					$destination[1]
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueAdder = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($untypedItem) use (&$source, &$defaults)
				{
					$sourceIndex = \array_search($untypedItem, $source->getArrayCopy(), true);
					return $defaults[$sourceIndex];
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[0])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[1])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[2])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[3])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[4])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source[5])->andReturnNull()->once();

				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[0])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[1])->andReturnNull()->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[2])->andReturn('id22')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[3])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[4])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination[5])->andReturn('id1')->once();

				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$sourceIndex = \array_search($a, $source->getArrayCopy(), true);
						$destinationIndex = is_array($destination) ? \array_search($b, $destination, true) : false;
						$result = ($sourceIndex !== false) && ($destinationIndex !== false);
						if ($result)
						{
							$b = $newDestination[$destinationIndex];
						}
						return $result;
					}
				)->times(\count($source));

				$strategy = new DT\Strategy\IdentifiableValueList(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueAdder,
					null,
					$untypedValueConstructor
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
	});
});
