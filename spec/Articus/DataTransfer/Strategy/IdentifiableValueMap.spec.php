<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;

\describe(DT\Strategy\IdentifiableValueMap::class, function ()
{
	\describe('->hydrate', function ()
	{
		\afterEach(function ()
		{
			\Mockery::close();
		});
		\it('throws on non iterable and non stdclass source', function ()
		{
			$source = \mock();
			$destination = null;
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException(\sprintf('Hydration can be done only from iterable or stdClass, not %s', \get_class($source)));

			$strategy = new DT\Strategy\IdentifiableValueMap(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null,
				false
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($exception);
		});
		\it('throws on non iterable and non stdclass destination', function ()
		{
			$source = [];
			$destination = \mock();
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException(\sprintf('Hydration can be done only to iterable or stdClass, not %s', \get_class($destination)));

			$strategy = new DT\Strategy\IdentifiableValueMap(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null,
				false
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($exception);
		});
		\it('wraps and rethrows invalid data exceptions from value strategy', function ()
		{
			$source = ['test' => \mock()];
			$destination = ['test' => \mock()];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$innerException = new DT\Exception\InvalidData(['violation123' => 456789]);
			$exception = new DT\Exception\InvalidData(
				[DT\Validator\Collection::INVALID_INNER => ['test' => $innerException->getViolations()]],
				$innerException
			);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($source['test'])->andReturn('test123')->once();
			$typedValueIdentifier->shouldReceive('__invoke')->with($destination['test'])->andReturn('test123')->once();
			$valueStrategy->shouldReceive('hydrate')->with($source['test'], $destination['test'])->andThrow($innerException);

			$strategy = new DT\Strategy\IdentifiableValueMap(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null,
				false
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
		\it('hydrates source item to destination item using value strategy if they have same key and same identifier', function ()
		{
			$sourceItem = \mock();
			$source = ['test' => &$sourceItem];
			$destinationItem = \mock();
			$destination = ['test' => &$destinationItem];
			$newDestinationItem = \mock();
			$newDestination = ['test' => &$newDestinationItem];
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

			$strategy = new DT\Strategy\IdentifiableValueMap(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null,
				false
			);

			$strategy->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\context('if there is typed value setter and there is typed value remover', function ()
		{
			\it('resets destination item and hydrates source item with same key to it using value strategy if items have distinct identifiers', function ()
			{
				$sourceItem = \mock();
				$source = ['test' =>  &$sourceItem];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$defaultDestinationItem = \mock();
				$newDestinationItem = \mock();
				$newDestination = ['test' => &$newDestinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(&$map, $key, $untypedItem) use (&$destination, &$sourceItem, &$defaultDestinationItem)
				{
					if (($destination !== $map) || ('test' !== $key) || ($sourceItem !== $untypedItem))
					{
						throw new \LogicException('Unexpected arguments for setter');
					}
					$map[$key] = &$defaultDestinationItem;
					return $defaultDestinationItem;
				};
				$typedValueRemover = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();
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
				)->once();

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('sets new destination item and hydrates source item to it using value strategy if there is no destination item with same key', function ()
			{
				$sourceItem = \mock();
				$source = ['test' =>  &$sourceItem];
				$destination = [];
				$defaultDestinationItem = \mock();
				$newDestinationItem = \mock();
				$newDestination = ['test' => &$newDestinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(&$map, $key, $untypedItem) use (&$destination, &$sourceItem, &$defaultDestinationItem)
				{
					if (($destination !== $map) || ('test' !== $key) || ($sourceItem !== $untypedItem))
					{
						throw new \LogicException('Unexpected arguments for setter');
					}
					$map[$key] = &$defaultDestinationItem;
					return $defaultDestinationItem;
				};
				$typedValueRemover = \mock(Example\InvokableInterface::class);

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

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('removes destination item if there is no source item with same key', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = ['test' =>  &$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	= \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);
				$typedValueRemover = function (&$map, $key) use (&$destination)
				{
					if (($map !== $destination) || ($key !== 'test'))
					{
						throw new \LogicException('Unexpected arguments for remover');
					}
					unset($map[$key]);
				};

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is typed value setter and there is no typed value remover', function ()
		{
			\it('resets destination item and hydrates source item with same key to it using value strategy if items have distinct identifiers', function ()
			{
				$sourceItem = \mock();
				$source = ['test' =>  &$sourceItem];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$defaultDestinationItem = \mock();
				$newDestinationItem = \mock();
				$newDestination = ['test' => &$newDestinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(&$map, $key, $untypedItem) use (&$destination, &$sourceItem, &$defaultDestinationItem)
				{
					if (($destination !== $map) || ('test' !== $key) || ($sourceItem !== $untypedItem))
					{
						throw new \LogicException('Unexpected arguments for setter');
					}
					$map[$key] = &$defaultDestinationItem;
					return $defaultDestinationItem;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();
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

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					null,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('sets new destination item and hydrates source item to it using value strategy if there is no destination item with same key', function ()
			{
				$sourceItem = \mock();
				$source = ['test' =>  &$sourceItem];
				$destination = [];
				$defaultDestinationItem = \mock();
				$newDestinationItem = \mock();
				$newDestination = ['test' => &$newDestinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(&$map, $key, $untypedItem) use (&$destination, &$sourceItem, &$defaultDestinationItem)
				{
					if (($destination !== $map) || ('test' !== $key) || ($sourceItem !== $untypedItem))
					{
						throw new \LogicException('Unexpected arguments for setter');
					}
					$map[$key] = &$defaultDestinationItem;
					return $defaultDestinationItem;
				};

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

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					null,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('keeps destination item if there is no source item with same key', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = ['test' =>  &$destinationItem];
				$newDestination = ['test' => &$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	= \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					null,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is no typed value setter and there is typed value remover', function ()
		{
			\it('removes destination item if source item with same key has distinct identifier', function ()
			{
				$sourceItem = \mock();
				$source = ['test' => &$sourceItem];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = function (&$list, $key) use (&$destination)
				{
					if (($list !== $destination) || ($key !== 'test'))
					{
						throw new \LogicException('Unexpected arguments for remover');
					}
					unset($list[$key]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('ignores source item if there is no destination item with same key', function ()
			{
				$sourceItem = \mock();
				$source = ['test' => &$sourceItem];
				$destination = [];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('removes destination item if there is no source item with same key', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = function (&$list, $key) use (&$destination)
				{
					if (($list !== $destination) || ($key !== 'test'))
					{
						throw new \LogicException('Unexpected arguments for remover');
					}
					unset($list[$key]);
				};

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is no typed value setter and there is no typed value remover', function ()
		{
			\it('ignores source item and keeps destination item with same key if items have distinct identifiers', function ()
			{
				$sourceItem = \mock();
				$source = ['test' =>  &$sourceItem];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$newDestination = ['test' => &$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('ignores source item if there is no destination item with same key', function ()
			{
				$sourceItem = \mock();
				$source = ['test' =>  &$sourceItem];
				$destination = [];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('keeps destination item if there is no source item same key', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$newDestination = ['test' => &$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});

		\context('complex positive scenarios', function ()
		{
			\it('hydrates array with several scalars', function ()
			{
				$source = [
					'si1' => 1,
					'si2' => 2,
					'sk1' => 3,
					'sk2' => 4,
					'uk1' => 5,
					'uk2' => 6,
				];
				$destination = [
					'uk4' => 10,
					'uk3' => 20,
					'sk2' => 30,
					'sk1' => 40,
					'si2' => 50,
					'si1' => 60,
				];
				$defaults = [
					'sk1' => 100,
					'sk2' => 200,
					'uk1' => 300,
					'uk2' => 400,
				];
				$newDestination = [
					'sk2' => 1000,
					'sk1' => 2000,
					'si2' => 3000,
					'si1' => 4000,
					'uk1' => 5000,
					'uk2' => 6000,
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(&$map, $key, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$map[$key] = &$defaults[$key];
					return $defaults[$key];
				};
				$typedValueRemover = function (&$map, $key) use (&$destination)
				{
					unset($map[$key]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si1'])->andReturn('id1')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si2'])->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk1'])->andReturn('id11')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['sk1'])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk2'])->andReturn('id21')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['sk2'])->andReturn('id22')->once();
				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = \array_search($a, $source, true);
						$result = ($key !== false) && (($defaults[$key] ?? $destination[$key] ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('hydrates array with several objects', function ()
			{
				$source = [
					'si1' => \mock(),
					'si2' => \mock(),
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];
				$destination = [
					'uk4' => \mock(),
					'uk3' => \mock(),
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
				];
				$defaults = [
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];
				$newDestination = [
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(&$map, $key, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$map[$key] = &$defaults[$key];
					return $defaults[$key];
				};
				$typedValueRemover = function (&$map, $key) use (&$destination)
				{
					unset($map[$key]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si1'])->andReturn('id1')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si2'])->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk1'])->andReturn('id11')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['sk1'])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk2'])->andReturn('id21')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['sk2'])->andReturn('id22')->once();
				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = \array_search($a, $source, true);
						$result = ($key !== false) && (($defaults[$key] ?? $destination[$key] ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('hydrates stdclass with several scalars', function ()
			{
				$source = new \stdClass();
				$source->si1 = 1;
				$source->si2 = 2;
				$source->sk1 = 3;
				$source->sk2 = 4;
				$source->uk1 = 5;
				$source->uk2 = 6;

				$destination = new \stdClass();
				$destination->uk4 = 10;
				$destination->uk3 = 20;
				$destination->sk2 = 30;
				$destination->sk1 = 40;
				$destination->si2 = 50;
				$destination->si1 = 60;

				$defaults = [
					'sk1' => 100,
					'sk2' => 200,
					'uk1' => 300,
					'uk2' => 400,
				];

				$newDestination = [
					'sk2' => 1000,
					'sk1' => 2000,
					'si2' => 3000,
					'si1' => 4000,
					'uk1' => 5000,
					'uk2' => 6000,
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(&$map, $key, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$map->{$key} = &$defaults[$key];
					return $defaults[$key];
				};
				$typedValueRemover = function (&$map, $key) use (&$destination)
				{
					unset($map->{$key});
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->si1)->andReturn('id1')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination->si1)->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->si2)->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination->si2)->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->sk1)->andReturn('id11')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination->sk1)->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->sk2)->andReturn('id21')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination->sk2)->andReturn('id22')->once();
				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = false;
						foreach ($source as $k => &$v)
						{
							if (($key === false) && ($v === $a))
							{
								$key = $k;
							}
						}
						$result = ($key !== false) && (($defaults[$key] ?? $destination->{$key} ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect((array) $destination)->toBe($newDestination);
			});
			\it('hydrates stdclass with several objects', function ()
			{
				$source = new \stdClass();
				$source->si1 = \mock();
				$source->si2 = \mock();
				$source->sk1 = \mock();
				$source->sk2 = \mock();
				$source->uk1 = \mock();
				$source->uk2 = \mock();

				$destination = new \stdClass();
				$destination->uk4 = \mock();
				$destination->uk3 = \mock();
				$destination->sk2 = \mock();
				$destination->sk1 = \mock();
				$destination->si2 = \mock();
				$destination->si1 = \mock();

				$defaults = [
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];

				$newDestination = [
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(&$map, $key, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$map->{$key} = &$defaults[$key];
					return $defaults[$key];
				};
				$typedValueRemover = function (&$map, $key) use (&$destination)
				{
					unset($map->{$key});
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->si1)->andReturn('id1')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination->si1)->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->si2)->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination->si2)->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->sk1)->andReturn('id11')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination->sk1)->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->sk2)->andReturn('id21')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination->sk2)->andReturn('id22')->once();
				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = false;
						foreach ($source as $k => &$v)
						{
							if (($key === false) && ($v === $a))
							{
								$key = $k;
							}
						}
						$result = ($key !== false) && (($defaults[$key] ?? $destination->{$key} ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect((array) $destination)->toBe($newDestination);
			});
			\it('hydrates array object with several scalars', function ()
			{
				$source = new \ArrayObject([
					'si1' => 1,
					'si2' => 2,
					'sk1' => 3,
					'sk2' => 4,
					'uk1' => 5,
					'uk2' => 6,
				]);
				$destination = new \ArrayObject([
					'uk4' => 10,
					'uk3' => 20,
					'sk2' => 30,
					'sk1' => 40,
					'si2' => 50,
					'si1' => 60,
				]);
				$defaults = [
					'sk1' => 100,
					'sk2' => 200,
					'uk1' => 300,
					'uk2' => 400,
				];
				$newDestination = [
					'sk2' => 1000,
					'sk1' => 2000,
					'si2' => 3000,
					'si1' => 4000,
					'uk1' => 5000,
					'uk2' => 6000,
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(\ArrayObject &$map, $key, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$map[$key] = &$defaults[$key];
					return $defaults[$key];
				};
				$typedValueRemover = function (\ArrayObject &$map, $key) use (&$destination)
				{
					unset($map[$key]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si1'])->andReturn('id1')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si2'])->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk1'])->andReturn('id11')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['sk1'])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk2'])->andReturn('id21')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['sk2'])->andReturn('id22')->once();
				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = \array_search($a, $source->getArrayCopy(), true);
						$result = ($key !== false) && (($defaults[$key] ?? $destination[$key] ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination->getArrayCopy())->toBe($newDestination);
			});
			\it('hydrates array object with several objects', function ()
			{
				$source = new \ArrayObject([
					'si1' => \mock(),
					'si2' => \mock(),
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				]);
				$destination = new \ArrayObject([
					'uk4' => \mock(),
					'uk3' => \mock(),
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
				]);
				$defaults = [
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];
				$newDestination = [
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = function &(\ArrayObject &$map, $key, $untypedItem) use (&$destination, &$source, &$defaults)
				{
					$map[$key] = &$defaults[$key];
					return $defaults[$key];
				};
				$typedValueRemover = function (\ArrayObject &$map, $key) use (&$destination)
				{
					unset($map[$key]);
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si1'])->andReturn('id1')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si2'])->andReturn('id2')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk1'])->andReturn('id11')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['sk1'])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk2'])->andReturn('id21')->once();
				$typedValueIdentifier->shouldReceive('__invoke')->with($destination['sk2'])->andReturn('id22')->once();
				$valueStrategy->shouldReceive('hydrate')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = \array_search($a, $source->getArrayCopy(), true);
						$result = ($key !== false) && (($defaults[$key] ?? $destination[$key] ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
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
		\it('throws on non iterable and non stdclass source', function ()
		{
			$source = \mock();
			$destination = null;
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException(\sprintf('Merge can be done only from iterable or stdClass, not %s', \get_class($source)));

			$strategy = new DT\Strategy\IdentifiableValueMap(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null,
				false
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->merge($source, $destination);
			})->toThrow($exception);
		});
		\it('throws on non iterable and non stdclass destination', function ()
		{
			$source = [];
			$destination = \mock();
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$exception = new \InvalidArgumentException(\sprintf('Merge can be done only to iterable or stdClass, not %s', \get_class($destination)));

			$strategy = new DT\Strategy\IdentifiableValueMap(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null,
				false
			);

			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->merge($source, $destination);
			})->toThrow($exception);
		});
		\it('wraps and rethrows invalid data exceptions from value strategy', function ()
		{
			$source = ['test' => \mock()];
			$destination = ['test' => \mock()];
			$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
			$typedValueIdentifier = \mock(Example\InvokableInterface::class);
			$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
			$innerException = new DT\Exception\InvalidData(['violation123' => 456789]);
			$exception = new DT\Exception\InvalidData(
				[DT\Validator\Collection::INVALID_INNER => ['test' => $innerException->getViolations()]],
				$innerException
			);

			$untypedValueIdentifier->shouldReceive('__invoke')->with($source['test'])->andReturn('test123')->once();
			$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['test'])->andReturn('test123')->once();
			$valueStrategy->shouldReceive('merge')->with($source['test'], $destination['test'])->andThrow($innerException);

			$strategy = new DT\Strategy\IdentifiableValueMap(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null,
				false
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
		\it('merges source item to destination item using value strategy if they have same key and same identifier', function ()
		{
			$sourceItem = \mock();
			$source = ['test' => &$sourceItem];
			$destinationItem = \mock();
			$destination = ['test' => &$destinationItem];
			$newDestinationItem = \mock();
			$newDestination = ['test' => &$newDestinationItem];
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

			$strategy = new DT\Strategy\IdentifiableValueMap(
				$valueStrategy,
				$typedValueIdentifier,
				$untypedValueIdentifier,
				null,
				null,
				null,
				false
			);

			$strategy->merge($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\context('if there is typed value setter and there is typed value remover', function ()
		{
			\context('if there is untyped value constructor', function ()
			{
				\it('resets destination item and merges source item with same key to it using value strategy if items have distinct identifiers', function ()
				{
					$sourceItem = \mock();
					$source = ['test' => &$sourceItem];
					$destinationItem = \mock();
					$destination = ['test' => &$destinationItem];
					$defaultDestinationItem = \mock();
					$newDestinationItem = \mock();
					$newDestination = ['test' => &$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueSetter = \mock(Example\InvokableInterface::class);
					$typedValueRemover = \mock(Example\InvokableInterface::class);
					$untypedValueConstructor = \mock(Example\InvokableInterface::class);

					$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
					$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();
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
					)->once();

					$strategy = new DT\Strategy\IdentifiableValueMap(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueSetter,
						$typedValueRemover,
						$untypedValueConstructor,
						false
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
				\it('sets new destination item and merges source item to it using value strategy if there is no destination item with same key', function ()
				{
					$sourceItem = \mock();
					$source = ['test' => &$sourceItem];
					$destination = [];
					$defaultDestinationItem = \mock();
					$newDestinationItem = \mock();
					$newDestination = ['test' => &$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueSetter = \mock(Example\InvokableInterface::class);
					$typedValueRemover = \mock(Example\InvokableInterface::class);
					$untypedValueConstructor = \mock(Example\InvokableInterface::class);

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
					)->once();

					$strategy = new DT\Strategy\IdentifiableValueMap(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueSetter,
						$typedValueRemover,
						$untypedValueConstructor,
						false
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
			});
			\context('if there is no untyped value constructor', function ()
			{
				\it('resets destination item and merges source item with same key to it using value strategy if items have distinct identifiers', function ()
				{
					$sourceItem = \mock();
					$source = ['test' => &$sourceItem];
					$destinationItem = \mock();
					$destination = ['test' => &$destinationItem];
					$newDestinationItem = \mock();
					$newDestination = ['test' => &$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueSetter = \mock(Example\InvokableInterface::class);
					$typedValueRemover = \mock(Example\InvokableInterface::class);

					$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
					$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();
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
					)->once();

					$strategy = new DT\Strategy\IdentifiableValueMap(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueSetter,
						$typedValueRemover,
						null,
						false
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
				\it('sets new destination item and merges source item to it using value strategy if there is no destination item with same key', function ()
				{
					$sourceItem = \mock();
					$source = ['test' => &$sourceItem];
					$destination = [];
					$newDestinationItem = \mock();
					$newDestination = ['test' => &$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueSetter = \mock(Example\InvokableInterface::class);
					$typedValueRemover = \mock(Example\InvokableInterface::class);

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
					)->once();

					$strategy = new DT\Strategy\IdentifiableValueMap(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueSetter,
						$typedValueRemover,
						null,
						false
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
			});
			\it('removes destination item if there is no source item with same key', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = ['test' =>  &$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	= \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);
				$typedValueRemover = function (&$map, $key) use (&$destination)
				{
					if (($map !== $destination) || ($key !== 'test'))
					{
						throw new \LogicException('Unexpected arguments for remover');
					}
					unset($map[$key]);
				};

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is typed value setter and there is no typed value remover', function ()
		{
			\context('if there is untyped value constructor', function ()
			{
				\it('resets destination item and merges source item with same key to it using value strategy if items have distinct identifiers', function ()
				{
					$sourceItem = \mock();
					$source = ['test' => &$sourceItem];
					$destinationItem = \mock();
					$destination = ['test' => &$destinationItem];
					$defaultDestinationItem = \mock();
					$newDestinationItem = \mock();
					$newDestination = ['test' => &$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueSetter = \mock(Example\InvokableInterface::class);;
					$untypedValueConstructor = \mock(Example\InvokableInterface::class);

					$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
					$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();
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
					)->once();

					$strategy = new DT\Strategy\IdentifiableValueMap(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueSetter,
						null,
						$untypedValueConstructor,
						false
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
				\it('sets new destination item and merges source item to it using value strategy if there is no destination item with same key', function ()
				{
					$sourceItem = \mock();
					$source = ['test' => &$sourceItem];
					$destination = [];
					$defaultDestinationItem = \mock();
					$newDestinationItem = \mock();
					$newDestination = ['test' => &$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueSetter = \mock(Example\InvokableInterface::class);
					$untypedValueConstructor = \mock(Example\InvokableInterface::class);

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
					)->once();

					$strategy = new DT\Strategy\IdentifiableValueMap(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueSetter,
						null,
						$untypedValueConstructor,
						false
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
			});
			\context('if there is no untyped value constructor', function ()
			{
				\it('resets destination item and merges source item with same key to it using value strategy if items have distinct identifiers', function ()
				{
					$sourceItem = \mock();
					$source = ['test' => &$sourceItem];
					$destinationItem = \mock();
					$destination = ['test' => &$destinationItem];
					$newDestinationItem = \mock();
					$newDestination = ['test' => &$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueSetter = \mock(Example\InvokableInterface::class);;

					$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
					$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();
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
					)->once();

					$strategy = new DT\Strategy\IdentifiableValueMap(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueSetter,
						null,
						null,
						false
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
				\it('sets new destination item and merges source item to it using value strategy if there is no destination item with same key', function ()
				{
					$sourceItem = \mock();
					$source = ['test' => &$sourceItem];
					$destination = [];
					$newDestinationItem = \mock();
					$newDestination = ['test' => &$newDestinationItem];
					$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
					$typedValueIdentifier = \mock(Example\InvokableInterface::class);
					$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
					$typedValueSetter = \mock(Example\InvokableInterface::class);

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
					)->once();

					$strategy = new DT\Strategy\IdentifiableValueMap(
						$valueStrategy,
						$typedValueIdentifier,
						$untypedValueIdentifier,
						$typedValueSetter,
						null,
						null,
						false
					);
					$strategy->merge($source, $destination);
					\expect($destination)->toBe($newDestination);
				});
			});
			\it('keeps destination item if there is no source item with same key', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = ['test' =>  &$destinationItem];
				$newDestination = ['test' => &$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	= \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					null,
					null,
					false
				);
				$strategy->hydrate($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is no typed value setter and there is typed value remover', function ()
		{
			\it('removes destination item if source item with same key has distinct identifier', function ()
			{
				$sourceItem = \mock();
				$source = ['test' => &$sourceItem];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('ignores source item if there is no destination item with same key', function ()
			{
				$sourceItem = \mock();
				$source = ['test' => &$sourceItem];
				$destination = [];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('removes destination item if there is no source item with same key', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					$typedValueRemover,
					null,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});
		\context('if there is no typed value setter and there is no typed value remover', function ()
		{
			\it('ignores source item and keeps destination item with same key if items have distinct identifiers', function ()
			{
				$sourceItem = \mock();
				$source = ['test' =>  &$sourceItem];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$newDestination = ['test' => &$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$untypedValueIdentifier->shouldReceive('__invoke')->with($sourceItem)->andReturn('test123')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destinationItem)->andReturn('test456')->once();

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('ignores source item if there is no destination item with same key', function ()
			{
				$sourceItem = \mock();
				$source = ['test' =>  &$sourceItem];
				$destination = [];
				$newDestination = [];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('keeps destination item if there is no source item same key', function ()
			{
				$source = [];
				$destinationItem = \mock();
				$destination = ['test' => &$destinationItem];
				$newDestination = ['test' => &$destinationItem];
				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					null,
					null,
					null,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
		});

		\context('complex positive scenarios', function ()
		{
			\it('merges array with several scalars', function ()
			{
				$source = [
					'si1' => 1,
					'si2' => 2,
					'sk1' => 3,
					'sk2' => 4,
					'uk1' => 5,
					'uk2' => 6,
				];
				$destination = [
					'uk4' => 10,
					'uk3' => 20,
					'sk2' => 30,
					'sk1' => 40,
					'si2' => 50,
					'si1' => 60,
				];
				$defaults = [
					'sk1' => 100,
					'sk2' => 200,
					'uk1' => 300,
					'uk2' => 400,
				];
				$newDestination = [
					'sk2' => 1000,
					'sk1' => 2000,
					'si2' => 3000,
					'si1' => 4000,
					'uk1' => 5000,
					'uk2' => 6000,
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($a) use (&$source, &$defaults)
				{
					$key = \array_search($a, $source, true);
					return $defaults[$key] ?? -1;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk1'])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['sk1'])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk2'])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['sk2'])->andReturn('id22')->once();
				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = \array_search($a, $source, true);
						$result = ($key !== false) && (($defaults[$key] ?? $destination[$key] ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					$untypedValueConstructor,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('merges array with several objects', function ()
			{
				$source = [
					'si1' => \mock(),
					'si2' => \mock(),
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];
				$destination = [
					'uk4' => \mock(),
					'uk3' => \mock(),
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
				];
				$defaults = [
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];
				$newDestination = [
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($a) use (&$source, &$defaults)
				{
					$key = \array_search($a, $source, true);
					return $defaults[$key] ?? -1;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk1'])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['sk1'])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk2'])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['sk2'])->andReturn('id22')->once();
				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = \array_search($a, $source, true);
						$result = ($key !== false) && (($defaults[$key] ?? $destination[$key] ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					$untypedValueConstructor,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination)->toBe($newDestination);
			});
			\it('merges stdclass with several scalars', function ()
			{
				$source = new \stdClass();
				$source->si1 = 1;
				$source->si2 = 2;
				$source->sk1 = 3;
				$source->sk2 = 4;
				$source->uk1 = 5;
				$source->uk2 = 6;

				$destination = new \stdClass();
				$destination->uk4 = 10;
				$destination->uk3 = 20;
				$destination->sk2 = 30;
				$destination->sk1 = 40;
				$destination->si2 = 50;
				$destination->si1 = 60;

				$defaults = [
					'sk1' => 100,
					'sk2' => 200,
					'uk1' => 300,
					'uk2' => 400,
				];

				$newDestination = [
					'sk2' => 1000,
					'sk1' => 2000,
					'si2' => 3000,
					'si1' => 4000,
					'uk1' => 5000,
					'uk2' => 6000,
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($a) use (&$source, &$defaults)
				{
					$key = false;
					foreach ($source as $k => &$v)
					{
						if (($key === false) && ($v === $a))
						{
							$key = $k;
						}
					}
					return $defaults[$key] ?? -1;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->si1)->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination->si1)->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->si2)->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination->si2)->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->sk1)->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination->sk1)->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->sk2)->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination->sk2)->andReturn('id22')->once();
				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = false;
						foreach ($source as $k => &$v)
						{
							if (($key === false) && ($v === $a))
							{
								$key = $k;
							}
						}
						$result = ($key !== false) && (($defaults[$key] ?? $destination->{$key} ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					$untypedValueConstructor,
					false
				);
				$strategy->merge($source, $destination);
				\expect((array) $destination)->toBe($newDestination);
			});
			\it('merges stdclass with several objects', function ()
			{
				$source = new \stdClass();
				$source->si1 = \mock();
				$source->si2 = \mock();
				$source->sk1 = \mock();
				$source->sk2 = \mock();
				$source->uk1 = \mock();
				$source->uk2 = \mock();

				$destination = new \stdClass();
				$destination->uk4 = \mock();
				$destination->uk3 = \mock();
				$destination->sk2 = \mock();
				$destination->sk1 = \mock();
				$destination->si2 = \mock();
				$destination->si1 = \mock();

				$defaults = [
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];

				$newDestination = [
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($a) use (&$source, &$defaults)
				{
					$key = false;
					foreach ($source as $k => &$v)
					{
						if (($key === false) && ($v === $a))
						{
							$key = $k;
						}
					}
					return $defaults[$key] ?? -1;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->si1)->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination->si1)->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->si2)->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination->si2)->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->sk1)->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination->sk1)->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source->sk2)->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination->sk2)->andReturn('id22')->once();
				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = false;
						foreach ($source as $k => &$v)
						{
							if (($key === false) && ($v === $a))
							{
								$key = $k;
							}
						}
						$result = ($key !== false) && (($defaults[$key] ?? $destination->{$key} ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					$untypedValueConstructor,
					false
				);
				$strategy->merge($source, $destination);
				\expect((array) $destination)->toBe($newDestination);
			});
			\it('merges array object with several scalars', function ()
			{
				$source = new \ArrayObject([
					'si1' => 1,
					'si2' => 2,
					'sk1' => 3,
					'sk2' => 4,
					'uk1' => 5,
					'uk2' => 6,
				]);
				$destination = new \ArrayObject([
					'uk4' => 10,
					'uk3' => 20,
					'sk2' => 30,
					'sk1' => 40,
					'si2' => 50,
					'si1' => 60,
				]);
				$defaults = [
					'sk1' => 100,
					'sk2' => 200,
					'uk1' => 300,
					'uk2' => 400,
				];
				$newDestination = [
					'sk2' => 1000,
					'sk1' => 2000,
					'si2' => 3000,
					'si1' => 4000,
					'uk1' => 5000,
					'uk2' => 6000,
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($a) use (&$source, &$defaults)
				{
					$key = \array_search($a, $source->getArrayCopy(), true);
					return $defaults[$key] ?? -1;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk1'])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['sk1'])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk2'])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['sk2'])->andReturn('id22')->once();
				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = \array_search($a, $source->getArrayCopy(), true);
						$result = ($key !== false) && (($defaults[$key] ?? $destination[$key] ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					$untypedValueConstructor,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination->getArrayCopy())->toBe($newDestination);
			});
			\it('merges array object with several objects', function ()
			{
				$source = new \ArrayObject([
					'si1' => \mock(),
					'si2' => \mock(),
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				]);
				$destination = new \ArrayObject([
					'uk4' => \mock(),
					'uk3' => \mock(),
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
				]);
				$defaults = [
					'sk1' => \mock(),
					'sk2' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];
				$newDestination = [
					'sk2' => \mock(),
					'sk1' => \mock(),
					'si2' => \mock(),
					'si1' => \mock(),
					'uk1' => \mock(),
					'uk2' => \mock(),
				];

				$valueStrategy = \mock(DT\Strategy\StrategyInterface::class);
				$typedValueIdentifier = \mock(Example\InvokableInterface::class);
				$untypedValueIdentifier	 = \mock(Example\InvokableInterface::class);
				$typedValueSetter = \mock(Example\InvokableInterface::class);
				$typedValueRemover = \mock(Example\InvokableInterface::class);
				$untypedValueConstructor = function ($a) use (&$source, &$defaults)
				{
					$key = \array_search($a, $source->getArrayCopy(), true);
					return $defaults[$key] ?? -1;
				};

				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['si1'])->andReturn('id1')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['si2'])->andReturn('id2')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk1'])->andReturn('id11')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['sk1'])->andReturn('id12')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($source['sk2'])->andReturn('id21')->once();
				$untypedValueIdentifier->shouldReceive('__invoke')->with($destination['sk2'])->andReturn('id22')->once();
				$valueStrategy->shouldReceive('merge')->withArgs(
					function ($a, &$b) use (&$source, &$destination, &$defaults, &$newDestination)
					{
						$key = \array_search($a, $source->getArrayCopy(), true);
						$result = ($key !== false) && (($defaults[$key] ?? $destination[$key] ?? -1) === $b);
						if ($result)
						{
							$b = $newDestination[$key];
						}
						return $result;
					}
				)->times(\count($newDestination));

				$strategy = new DT\Strategy\IdentifiableValueMap(
					$valueStrategy,
					$typedValueIdentifier,
					$untypedValueIdentifier,
					$typedValueSetter,
					$typedValueRemover,
					$untypedValueConstructor,
					false
				);
				$strategy->merge($source, $destination);
				\expect($destination->getArrayCopy())->toBe($newDestination);
			});
		});
	});
});
