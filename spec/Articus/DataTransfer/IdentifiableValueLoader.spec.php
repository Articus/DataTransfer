<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer;

use spec\Example;
use Articus\DataTransfer as DT;

\describe(DT\IdentifiableValueLoader::class, function ()
{
	\afterEach(function ()
	{
		\Mockery::close();
	});
	//TODO test string ids
	//TODO test scalar values
	\describe('identification', function ()
	{
		\it('delegates to identifier getter for type', function ()
		{
			$type1 = 'test1';
			$type2 = 'test2';
			$id = 123;
			$value = \mock();
			$valueWithoutId = 456;
			$idGetter1 = \mock(Example\InvokableInterface::class);
			$valueLoader1 = \mock(Example\InvokableInterface::class);
			$idGetter2 = \mock(Example\InvokableInterface::class);
			$valueLoader2 = \mock(Example\InvokableInterface::class);
			$types = [$type1 => [$idGetter1, $valueLoader1], $type2 => [$idGetter2, $valueLoader2]];

			$idGetter1->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
			$idGetter1->shouldReceive('__invoke')->with($valueWithoutId)->andReturn(null)->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect($service->identify($type1, $value))->toBe($id);
			\expect($service->identify($type1, $valueWithoutId))->toBeNull();
		});
		\it('throws on unknown type', function ()
		{
			$type = 'unknown';
			$error = new \LogicException(\sprintf('Unknown type "%s"', $type));

			$service = new DT\IdentifiableValueLoader([]);
			\expect(function () use ($service, $type)
			{
				$service->identify($type, \mock());
			})->toThrow($error);
		});
	});
	\describe('banking', function ()
	{
		\it('gets banked value that was banked before load', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();

			$service = new DT\IdentifiableValueLoader($types);
			$service->bank($type, $value);
			\expect($service->get($type, $id))->toBe($value);
		});
		\it('gets banked value that was banked after load', function ()
		{
			$type = 'test';
			$id = 123;
			$loadedValue = \mock();
			$bankedValue = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($loadedValue)->andReturn($id)->once();
			$idGetter->shouldReceive('__invoke')->with($bankedValue)->andReturn($id)->once();
			$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$loadedValue])->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect($service->get($type, $id))->toBe($loadedValue);
			$service->bank($type, $bankedValue);
			\expect($service->get($type, $id))->toBe($bankedValue);
		});
		\it('throws on value without identifier', function ()
		{
			$type = 'test';
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];
			$error = new \LogicException('Banked value does not have identifier');

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn(null)->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect(function () use ($service, $type, $value)
			{
				$service->bank($type, $value);
			})->toThrow($error);
		});
	});
	\describe('wishing', function ()
	{
		\it('loads all wished values on first get for type', function ()
		{
			$type1 = 'test1';
			$id11 = 1123;
			$id12 = 456;
			$id13 = 1789;
			$value11 = \mock();
			$value12 = \mock();
			$value13 = \mock();
			$idGetter1 = \mock(Example\InvokableInterface::class);
			$valueLoader1 = \mock(Example\InvokableInterface::class);
			$type2 = 'test2';
			$id21 = 2123;
			$id22 = 456;
			$id23 = 2789;
			$value21 = \mock();
			$value22 = \mock();
			$value23 = \mock();
			$idGetter2 = \mock(Example\InvokableInterface::class);
			$valueLoader2 = \mock(Example\InvokableInterface::class);
			$types = [$type1 => [$idGetter1, $valueLoader1], $type2 => [$idGetter2, $valueLoader2]];

			$idGetter1->shouldReceive('__invoke')->with($value11)->andReturn($id11)->once();
			$idGetter1->shouldReceive('__invoke')->with($value12)->andReturn($id12)->once();
			$idGetter1->shouldReceive('__invoke')->with($value13)->andReturn($id13)->once();
			$valueLoader1->shouldReceive('__invoke')->with([$id11, $id12, $id13])->andReturn([$value11, $value12, $value13])->once();
			$idGetter2->shouldReceive('__invoke')->with($value21)->andReturn($id21)->once();
			$idGetter2->shouldReceive('__invoke')->with($value22)->andReturn($id22)->once();
			$idGetter2->shouldReceive('__invoke')->with($value23)->andReturn($id23)->once();
			$valueLoader2->shouldReceive('__invoke')->with([$id21, $id22, $id23])->andReturn([$value21, $value22, $value23])->once();

			$service = new DT\IdentifiableValueLoader($types);
			$service->wish($type1, $id11);
			$service->wishMultiple($type1, [$id12, $id13]);
			$service->wishMultiple($type2, [$id21, $id22]);
			$service->wish($type2, $id23);
			\expect(\iterator_to_array($service->getMultiple($type1, [$id11, $id12])))->toBe([$id11 => $value11, $id12 => $value12]);
			\expect($service->get($type1, $id13))-> toBe($value13);
			\expect($service->get($type2, $id21))-> toBe($value21);
			\expect(\iterator_to_array($service->getMultiple($type2, [$id22, $id23])))->toBe([$id22 => $value22, $id23 => $value23]);
		});
		\it('does not load wished value that was loaded before wish', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
			$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect($service->get($type, $id))->toBe($value);
			$service->wish($type, $id);
			\expect($service->get($type, $id))->toBe($value);
		});
		\it('does not load wished value that was banked before wish', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();

			$service = new DT\IdentifiableValueLoader($types);
			$service->bank($type, $value);
			$service->wish($type, $id);
			\expect($service->get($type, $id))->toBe($value);
		});
		\it('does not load wished value that was banked after wish but before get', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();

			$service = new DT\IdentifiableValueLoader($types);
			$service->wish($type, $id);
			$service->bank($type, $value);
			\expect($service->get($type, $id))->toBe($value);
		});
		\it('loads wished value once if it was wished several times before get', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
			$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();

			$service = new DT\IdentifiableValueLoader($types);
			$service->wish($type, $id);
			$service->wishMultiple($type, [$id]);
			\expect($service->get($type, $id))->toBe($value);
		});
		\it('ignores duplicate identifiers', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
			$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();

			$service = new DT\IdentifiableValueLoader($types);
			$service->wishMultiple($type, [$id, $id]);
			\expect($service->get($type, $id))->toBe($value);
		});
	});
	\describe('getting', function ()
	{
		\it('delegates to value loader for type', function ()
		{
			$type1 = 'test1';
			$type2 = 'test2';
			$id = 123;
			$idWithoutValue = 456;
			$value = \mock();
			$idGetter1 = \mock(Example\InvokableInterface::class);
			$valueLoader1 = \mock(Example\InvokableInterface::class);
			$idGetter2 = \mock(Example\InvokableInterface::class);
			$valueLoader2 = \mock(Example\InvokableInterface::class);
			$types = [$type1 => [$idGetter1, $valueLoader1], $type2 => [$idGetter2, $valueLoader2]];

			$idGetter1->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
			$valueLoader1->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();
			$valueLoader1->shouldReceive('__invoke')->with([$idWithoutValue])->andReturn([])->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect($service->get($type1, $id))->toBe($value);
			\expect($service->get($type1, $idWithoutValue))->toBeNull();
		});
		\it('does not load value that was got before get', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
			$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect($service->get($type, $id))->toBe($value);
			\expect(\iterator_to_array($service->getMultiple($type, [$id])))->toBe([$id => $value]);
		});
		\it('does not load value that was not got successfully before get', function ()
		{
			$type = 'test';
			$id = 123;
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([])->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect($service->get($type, $id))->toBeNull();
			\expect(\iterator_to_array($service->getMultiple($type, [$id])))->toBe([]);
		});
		\it('ignores duplicate identifiers', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
			$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn(
				(function() use (&$value)
				{
					yield $value;
				})()
			)->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect(\iterator_to_array($service->getMultiple($type, [$id, $id])))->toBe([$id => $value]);
		});
		\it('does not load "bonus" value that was got before get', function ()
		{
			$type = 'test';
			$id1 = 123;
			$id2 = 456;
			$value1 = \mock();
			$value2 = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];

			$idGetter->shouldReceive('__invoke')->with($value1)->andReturn($id1)->once();
			$idGetter->shouldReceive('__invoke')->with($value2)->andReturn($id2)->once();
			$valueLoader->shouldReceive('__invoke')->with([$id1])->andReturn([$value1, $value2])->once();

			$service = new DT\IdentifiableValueLoader($types);
			\expect($service->get($type, $id1))->toBe($value1);
			\expect($service->get($type, $id2))->toBe($value2);
		});
		\it('throws on unknown type', function ()
		{
			$type = 'unknown';
			$error = new \LogicException(\sprintf('Unknown type "%s"', $type));

			$service = new DT\IdentifiableValueLoader([]);

			\expect(function () use ($service, $type)
			{
				$service->get($type, 123);
			})->toThrow($error);

			\expect(function () use ($service, $type)
			{
				\iterator_to_array($service->getMultiple($type, [456, '789']));
			})->toThrow($error);
		});
		\it('throws if value loader returns value without id', function ()
		{
			$type = 'test';
			$id = 123;
			$value = \mock();
			$idGetter = \mock(Example\InvokableInterface::class);
			$valueLoader = \mock(Example\InvokableInterface::class);
			$types = [$type => [$idGetter, $valueLoader]];
			$error = new \LogicException('Loaded value does not have identifier');

			$idGetter->shouldReceive('__invoke')->with($value)->andReturn(null)->once();
			$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();

			$service = new DT\IdentifiableValueLoader($types);

			\expect(function () use ($service, $type, $id)
			{
				$service->get($type, $id);
			})->toThrow($error);
		});
	});
	\describe('forgetting', function ()
	{
		\describe('for type and id', function ()
		{
			\it('forgets got value', function ()
			{
				$type = 'test1';
				$id = 123;
				$value = \mock();
				$idGetter = \mock(Example\InvokableInterface::class);
				$valueLoader = \mock(Example\InvokableInterface::class);
				$types = [$type => [$idGetter, $valueLoader]];

				$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->twice();
				$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->twice();

				$service = new DT\IdentifiableValueLoader($types);
				\expect($service->get($type, $id))->toBe($value);
				$service->forget($type, $id);
				\expect($service->get($type, $id))->toBe($value);
			});
			\it('forgets value that was not got successfully', function ()
			{
				$type = 'test1';
				$id = 123;
				$idGetter = \mock(Example\InvokableInterface::class);
				$valueLoader = \mock(Example\InvokableInterface::class);
				$types = [$type => [$idGetter, $valueLoader]];

				$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([])->twice();

				$service = new DT\IdentifiableValueLoader($types);
				\expect($service->get($type, $id))->toBeNull();
				$service->forget($type, $id);
				\expect($service->get($type, $id))->toBeNull();
			});
			\it('forgets banked value', function ()
			{
				$type = 'test';
				$id = 123;
				$value = \mock();
				$idGetter = \mock(Example\InvokableInterface::class);
				$valueLoader = \mock(Example\InvokableInterface::class);
				$types = [$type => [$idGetter, $valueLoader]];

				$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->twice();
				$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();

				$service = new DT\IdentifiableValueLoader($types);
				$service->bank($type, $value);
				$service->forget($type, $id);
				\expect($service->get($type, $id))->toBe($value);
			});
			\it('forgets wished value', function ()
			{
				$type = 'test';
				$wishedId = 456;
				$id = 123;
				$value = \mock();
				$idGetter = \mock(Example\InvokableInterface::class);
				$valueLoader = \mock(Example\InvokableInterface::class);
				$types = [$type => [$idGetter, $valueLoader]];

				$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
				$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();

				$service = new DT\IdentifiableValueLoader($types);
				$service->wish($type, $wishedId);
				$service->forget($type, $wishedId);
				\expect($service->get($type, $id))->toBe($value);
			});
			//TODO check that forget for one id does not affect other ids of same type
			//TODO check that forget for one type does not affect other types
		});
		\describe('for type', function ()
		{
			\it('forgets got values', function ()
			{
				$type = 'test';
				$id1 = 123;
				$value1 = \mock();
				$id2 = 456;
				$value2 = \mock();
				$idGetter = \mock(Example\InvokableInterface::class);
				$valueLoader = \mock(Example\InvokableInterface::class);
				$types = [$type => [$idGetter, $valueLoader]];

				$idGetter->shouldReceive('__invoke')->with($value1)->andReturn($id1)->twice();
				$idGetter->shouldReceive('__invoke')->with($value2)->andReturn($id2)->twice();
				$valueLoader->shouldReceive('__invoke')->with([$id1])->andReturn([$value1])->twice();
				$valueLoader->shouldReceive('__invoke')->with([$id2])->andReturn([$value2])->twice();

				$service = new DT\IdentifiableValueLoader($types);
				\expect($service->get($type, $id1))->toBe($value1);
				\expect($service->get($type, $id2))->toBe($value2);
				$service->forget($type);
				\expect($service->get($type, $id1))->toBe($value1);
				\expect($service->get($type, $id2))->toBe($value2);
			});
			\it('forgets values that were not got successfully', function ()
			{
				$type = 'test';
				$id1 = 123;
				$id2 = 456;
				$idGetter = \mock(Example\InvokableInterface::class);
				$valueLoader = \mock(Example\InvokableInterface::class);
				$types = [$type => [$idGetter, $valueLoader]];

				$valueLoader->shouldReceive('__invoke')->with([$id1])->andReturn([])->twice();
				$valueLoader->shouldReceive('__invoke')->with([$id2])->andReturn([])->twice();

				$service = new DT\IdentifiableValueLoader($types);
				\expect($service->get($type, $id1))->toBeNull();
				\expect($service->get($type, $id2))->toBeNull();
				$service->forget($type);
				\expect($service->get($type, $id1))->toBeNull();
				\expect($service->get($type, $id2))->toBeNull();
			});
			\it('forgets banked values', function ()
			{
				$type = 'test';
				$id1 = 123;
				$value1 = \mock();
				$id2 = 456;
				$value2 = \mock();
				$idGetter = \mock(Example\InvokableInterface::class);
				$valueLoader = \mock(Example\InvokableInterface::class);
				$types = [$type => [$idGetter, $valueLoader]];

				$idGetter->shouldReceive('__invoke')->with($value1)->andReturn($id1)->twice();
				$idGetter->shouldReceive('__invoke')->with($value2)->andReturn($id2)->twice();
				$valueLoader->shouldReceive('__invoke')->with([$id1])->andReturn([$value1])->once();
				$valueLoader->shouldReceive('__invoke')->with([$id2])->andReturn([$value2])->once();

				$service = new DT\IdentifiableValueLoader($types);
				$service->bank($type, $value1);
				$service->bank($type, $value2);
				$service->forget($type);
				\expect($service->get($type, $id1))->toBe($value1);
				\expect($service->get($type, $id2))->toBe($value2);
			});
			\it('forgets wished values', function ()
			{
				$type = 'test';
				$wishedId1 = 456;
				$wishedId2 = 789;
				$id = 123;
				$value = \mock();
				$idGetter = \mock(Example\InvokableInterface::class);
				$valueLoader = \mock(Example\InvokableInterface::class);
				$types = [$type => [$idGetter, $valueLoader]];

				$idGetter->shouldReceive('__invoke')->with($value)->andReturn($id)->once();
				$valueLoader->shouldReceive('__invoke')->with([$id])->andReturn([$value])->once();

				$service = new DT\IdentifiableValueLoader($types);
				$service->wish($type, $wishedId1);
				$service->wish($type, $wishedId2);
				$service->forget($type);
				\expect($service->get($type, $id))->toBe($value);
			});
			//TODO check that forget for one type does not affect other types
		});
		\describe('for all', function ()
		{
			\it('forgets got values', function ()
			{
				$type1 = 'test1';
				$id11 = 1123;
				$value11 = \mock();
				$id12 = 456;
				$value12 = \mock();
				$idGetter1 = \mock(Example\InvokableInterface::class);
				$valueLoader1 = \mock(Example\InvokableInterface::class);
				$type2 = 'test2';
				$id21 = 2123;
				$value21 = \mock();
				$id22 = 456;
				$value22 = \mock();
				$idGetter2 = \mock(Example\InvokableInterface::class);
				$valueLoader2 = \mock(Example\InvokableInterface::class);
				$types = [$type1 => [$idGetter1, $valueLoader1], $type2 => [$idGetter2, $valueLoader2]];

				$idGetter1->shouldReceive('__invoke')->with($value11)->andReturn($id11)->twice();
				$idGetter1->shouldReceive('__invoke')->with($value12)->andReturn($id12)->twice();
				$idGetter2->shouldReceive('__invoke')->with($value21)->andReturn($id21)->twice();
				$idGetter2->shouldReceive('__invoke')->with($value22)->andReturn($id22)->twice();
				$valueLoader1->shouldReceive('__invoke')->with([$id11, $id12])->andReturn([$value11, $value12])->twice();
				$valueLoader2->shouldReceive('__invoke')->with([$id21, $id22])->andReturn([$value21, $value22])->twice();

				$service = new DT\IdentifiableValueLoader($types);
				\expect(\iterator_to_array($service->getMultiple($type1, [$id11, $id12])))->toBe([$id11 => $value11, $id12 => $value12]);
				\expect(\iterator_to_array($service->getMultiple($type2, [$id21, $id22])))->toBe([$id21 => $value21, $id22 => $value22]);
				$service->forgetAll();
				\expect(\iterator_to_array($service->getMultiple($type1, [$id11, $id12])))->toBe([$id11 => $value11, $id12 => $value12]);
				\expect(\iterator_to_array($service->getMultiple($type2, [$id21, $id22])))->toBe([$id21 => $value21, $id22 => $value22]);
			});
			\it('forgets values that were not got successfully', function ()
			{
				$type1 = 'test1';
				$id11 = 1123;
				$id12 = 456;
				$idGetter1 = \mock(Example\InvokableInterface::class);
				$valueLoader1 = \mock(Example\InvokableInterface::class);
				$type2 = 'test2';
				$id21 = 2123;
				$id22 = 456;
				$idGetter2 = \mock(Example\InvokableInterface::class);
				$valueLoader2 = \mock(Example\InvokableInterface::class);
				$types = [$type1 => [$idGetter1, $valueLoader1], $type2 => [$idGetter2, $valueLoader2]];

				$valueLoader1->shouldReceive('__invoke')->with([$id11, $id12])->andReturn([])->twice();
				$valueLoader2->shouldReceive('__invoke')->with([$id21, $id22])->andReturn([])->twice();

				$service = new DT\IdentifiableValueLoader($types);
				\expect(\iterator_to_array($service->getMultiple($type1, [$id11, $id12])))->toBe([]);
				\expect(\iterator_to_array($service->getMultiple($type2, [$id21, $id22])))->toBe([]);
				$service->forgetAll();
				\expect(\iterator_to_array($service->getMultiple($type1, [$id11, $id12])))->toBe([]);
				\expect(\iterator_to_array($service->getMultiple($type2, [$id21, $id22])))->toBe([]);
			});
			\it('forgets banked values', function ()
			{
				$type1 = 'test1';
				$id11 = 1123;
				$value11 = \mock();
				$id12 = 456;
				$value12 = \mock();
				$idGetter1 = \mock(Example\InvokableInterface::class);
				$valueLoader1 = \mock(Example\InvokableInterface::class);
				$type2 = 'test2';
				$id21 = 2123;
				$value21 = \mock();
				$id22 = 456;
				$value22 = \mock();
				$idGetter2 = \mock(Example\InvokableInterface::class);
				$valueLoader2 = \mock(Example\InvokableInterface::class);
				$types = [$type1 => [$idGetter1, $valueLoader1], $type2 => [$idGetter2, $valueLoader2]];

				$idGetter1->shouldReceive('__invoke')->with($value11)->andReturn($id11)->twice();
				$idGetter1->shouldReceive('__invoke')->with($value12)->andReturn($id12)->twice();
				$idGetter2->shouldReceive('__invoke')->with($value21)->andReturn($id21)->twice();
				$idGetter2->shouldReceive('__invoke')->with($value22)->andReturn($id22)->twice();
				$valueLoader1->shouldReceive('__invoke')->with([$id11, $id12])->andReturn([$value11, $value12])->once();
				$valueLoader2->shouldReceive('__invoke')->with([$id21, $id22])->andReturn([$value21, $value22])->once();

				$service = new DT\IdentifiableValueLoader($types);
				$service->bank($type1, $value11);
				$service->bank($type1, $value12);
				$service->bank($type2, $value21);
				$service->bank($type2, $value22);
				$service->forgetAll();
				\expect(\iterator_to_array($service->getMultiple($type1, [$id11, $id12])))->toBe([$id11 => $value11, $id12 => $value12]);
				\expect(\iterator_to_array($service->getMultiple($type2, [$id21, $id22])))->toBe([$id21 => $value21, $id22 => $value22]);
			});
			\it('forgets wished values', function ()
			{
				$type1 = 'test1';
				$wishedId11 = 1456;
				$wishedId12 = 789;
				$id1 = 1123;
				$value1 = \mock();
				$idGetter1 = \mock(Example\InvokableInterface::class);
				$valueLoader1 = \mock(Example\InvokableInterface::class);
				$type2 = 'test2';
				$wishedId21 = 2456;
				$wishedId22 = 789;
				$id2 = 2123;
				$value2 = \mock();
				$idGetter2 = \mock(Example\InvokableInterface::class);
				$valueLoader2 = \mock(Example\InvokableInterface::class);
				$types = [$type1 => [$idGetter1, $valueLoader1], $type2 => [$idGetter2, $valueLoader2]];

				$idGetter1->shouldReceive('__invoke')->with($value1)->andReturn($id1)->once();
				$valueLoader1->shouldReceive('__invoke')->with([$id1])->andReturn([$value1])->once();
				$idGetter2->shouldReceive('__invoke')->with($value2)->andReturn($id2)->once();
				$valueLoader2->shouldReceive('__invoke')->with([$id2])->andReturn([$value2])->once();

				$service = new DT\IdentifiableValueLoader($types);
				$service->wishMultiple($type1, [$wishedId11, $wishedId12]);
				$service->wishMultiple($type2, [$wishedId21, $wishedId22]);
				$service->forgetAll();
				\expect($service->get($type1, $id1))->toBe($value1);
				\expect($service->get($type2, $id2))->toBe($value2);
			});
			//TODO check that forget for one type does not affect other types
		});
	});
});
