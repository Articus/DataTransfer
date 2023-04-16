<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use spec\Example;

describe(DT\Strategy\FieldData::class, function ()
{
	describe('->hydrate', function ()
	{
		afterEach(function ()
		{
			Mockery::close();
		});
		it('hydrates array to object', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [$fieldName1 => $from1, $fieldName2 => $from2];
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($newTo1);
			expect($destination->getTest2())->toBe($newTo2);
		});
		it('hydrates stdClass to object', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$source->{$fieldName1} = $from1;
			$source->{$fieldName2} = $from2;
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($newTo1);
			expect($destination->getTest2())->toBe($newTo2);
		});
		it('hydrates ArrayAccess to object', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldReceive('offsetExists')->with($fieldName1)->once()->andReturn(true);
			$source->shouldReceive('offsetExists')->with($fieldName2)->once()->andReturn(true);
			$source->shouldReceive('offsetGet')->with($fieldName1)->once()->andReturn($from1);
			$source->shouldReceive('offsetGet')->with($fieldName2)->once()->andReturn($from2);
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($newTo1);
			expect($destination->getTest2())->toBe($newTo2);
		});
		it('does not hydrate to object fields that do not exist in array', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->never();
			$strategy2->shouldReceive('hydrate')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [];
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($newTo1);
			expect($destination->getTest2())->toBe($newTo2);
		});
		it('does not hydrate to object fields that do not exist in stdClass', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->never();
			$strategy2->shouldReceive('hydrate')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($newTo1);
			expect($destination->getTest2())->toBe($newTo2);
		});
		it('does not hydrate to object fields that do not exist in ArrayAccess', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->never();
			$strategy2->shouldReceive('hydrate')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldReceive('offsetExists')->with($fieldName1)->once()->andReturn(false);
			$source->shouldReceive('offsetExists')->with($fieldName2)->once()->andReturn(false);
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($newTo1);
			expect($destination->getTest2())->toBe($newTo2);
		});
		it('does not hydrate from array to object fields without setters', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->never();
			$strategy2->shouldReceive('hydrate')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [$fieldName1 => $from1, $fieldName2 => $from2];
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($oldTo1);
			expect($destination->getTest2())->toBe($oldTo2);
		});
		it('does not hydrate from stdClass to object fields without setters', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->never();
			$strategy2->shouldReceive('hydrate')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$source->{$fieldName1} = $from1;
			$source->{$fieldName2} = $from2;
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($oldTo1);
			expect($destination->getTest2())->toBe($oldTo2);
		});
		it('does not hydrate from ArrayAccess to object fields without setters', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->never();
			$strategy2->shouldReceive('hydrate')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldNotReceive('offsetExists')->with($fieldName1);
			$source->shouldNotReceive('offsetExists')->with($fieldName2);
			$source->shouldNotReceive('offsetGet')->with($fieldName1);
			$source->shouldNotReceive('offsetGet')->with($fieldName2);
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$destination->setTest2($oldTo2);
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->hydrate($source, $destination);
			expect($destination->test1)->toBe($oldTo1);
			expect($destination->getTest2())->toBe($oldTo2);
		});
		it('throws on source that is not map', function ()
		{
			$source = mock();
			$destination = new Example\DTO\Data();
			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, [], false);
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
					sprintf('Hydration can be done only from key-value map, not %s', get_class($source))
				);
			}
		});
		it('throws on destination of invalid type', function ()
		{
			$source = [];
			$destination = mock();
			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, [], false);
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
					sprintf('Hydration can be done only to %s, not %s', Example\DTO\Data::class, get_class($destination))
				);
			}
		});
		it('rethrows wrapped invalid data exception', function ()
		{
			$from1 = 'a';
			$oldTo1 = 'b';

			$violations = ['test' => 123];
			$innerError = new DT\Exception\InvalidData($violations);

			$fieldStrategy = mock(DT\Strategy\StrategyInterface::class);
			$fieldStrategy->shouldReceive('hydrate')->with($from1, $oldTo1)->once()->andThrow($innerError);

			$fieldName1 = 'test_1';
			$source = [$fieldName1 => $from1];
			$destination = new Example\DTO\Data();
			$destination->test1 = $oldTo1;
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $fieldStrategy],
			];
			$error = new DT\Exception\InvalidData([DT\Validator\FieldData::INVALID_INNER => [$fieldName1 => $violations]], $innerError);

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($error);
		});
	});
	describe('->merge', function ()
	{
		afterEach(function ()
		{
			Mockery::close();
		});
		it('merges array to array', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [$fieldName1 => $from1, $fieldName2 => $from2];
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $newTo1, $fieldName2 => $newTo2]);
		});
		it('merges stdClass to array', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$source->{$fieldName1} = $from1;
			$source->{$fieldName2} = $from2;
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $newTo1, $fieldName2 => $newTo2]);
		});
		it('merges ArrayAccess to array', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldReceive('offsetExists')->with($fieldName1)->once()->andReturn(true);
			$source->shouldReceive('offsetExists')->with($fieldName2)->once()->andReturn(true);
			$source->shouldReceive('offsetGet')->with($fieldName1)->once()->andReturn($from1);
			$source->shouldReceive('offsetGet')->with($fieldName2)->once()->andReturn($from2);
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $newTo1, $fieldName2 => $newTo2]);
		});
		it('merges array to stdClass', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [$fieldName1 => $from1, $fieldName2 => $from2];
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($newTo1);
			expect($destination->{$fieldName2})->toBe($newTo2);
		});
		it('merges stdClass to stdClass', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$source->{$fieldName1} = $from1;
			$source->{$fieldName2} = $from2;
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($newTo1);
			expect($destination->{$fieldName2})->toBe($newTo2);
		});
		it('merges ArrayAccess to stdClass', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from1, &$oldTo1, &$newTo1)
				{
					$result = ($a === $from1) && ($b === $oldTo1);
					if ($result)
					{
						$b = $newTo1;
					}
					return $result;
				}
			)->once();
			$strategy2->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$from2, &$oldTo2, &$newTo2)
				{
					$result = ($a === $from2) && ($b === $oldTo2);
					if ($result)
					{
						$b = $newTo2;
					}
					return $result;
				}
			)->once();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldReceive('offsetExists')->with($fieldName1)->once()->andReturn(true);
			$source->shouldReceive('offsetExists')->with($fieldName2)->once()->andReturn(true);
			$source->shouldReceive('offsetGet')->with($fieldName1)->once()->andReturn($from1);
			$source->shouldReceive('offsetGet')->with($fieldName2)->once()->andReturn($from2);
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($newTo1);
			expect($destination->{$fieldName2})->toBe($newTo2);
		});
		it('does not merge to array fields that do not exist in array', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [];
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $newTo1, $fieldName2 => $newTo2]);
		});
		it('does not merge to array fields that do not exist in stdClass', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $newTo1, $fieldName2 => $newTo2]);
		});
		it('does not merge to array fields that do not exist in ArrayAccess', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldReceive('offsetExists')->with($fieldName1)->once()->andReturn(false);
			$source->shouldReceive('offsetExists')->with($fieldName2)->once()->andReturn(false);
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $newTo1, $fieldName2 => $newTo2]);
		});
		it('does not merge to stdClass fields that do not exist in array', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [];
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($newTo1);
			expect($destination->{$fieldName2})->toBe($newTo2);
		});
		it('does not merge to stdClass fields that do not exist in stdClass', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('hydrate')->never();
			$strategy2->shouldReceive('hydrate')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($newTo1);
			expect($destination->{$fieldName2})->toBe($newTo2);
		});
		it('does not merge to stdClass fields that do not exist in ArrayAccess', function ()
		{
			$oldTo1 = 'a';
			$oldTo2 = 'b';
			$newTo1 = $oldTo1;
			$newTo2 = $oldTo2;

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldReceive('offsetExists')->with($fieldName1)->once()->andReturn(false);
			$source->shouldReceive('offsetExists')->with($fieldName2)->once()->andReturn(false);
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $strategy1],
				[$fieldName2, ['getTest2', true], ['setTest2', true], $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($newTo1);
			expect($destination->{$fieldName2})->toBe($newTo2);
		});
		it('does not merge from array to array fields without setters', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [$fieldName1 => $from1, $fieldName2 => $from2];
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $oldTo1, $fieldName2 => $oldTo2]);
		});
		it('does not merge from stdClass to array fields without setters', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$source->{$fieldName1} = $from1;
			$source->{$fieldName2} = $from2;
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $oldTo1, $fieldName2 => $oldTo2]);
		});
		it('does not merge from ArrayAccess to array fields without setters', function ()
		{
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldNotReceive('offsetExists')->with($fieldName1);
			$source->shouldNotReceive('offsetExists')->with($fieldName2);
			$source->shouldNotReceive('offsetGet')->with($fieldName1);
			$source->shouldNotReceive('offsetGet')->with($fieldName2);
			$destination = [$fieldName1 => $oldTo1, $fieldName2 => $oldTo2];
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination)->toBe([$fieldName1 => $oldTo1, $fieldName2 => $oldTo2]);
		});
		it('does not merge from array to stdClass fields without setters', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = [$fieldName1 => $from1, $fieldName2 => $from2];
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($oldTo1);
			expect($destination->{$fieldName2})->toBe($oldTo2);
		});
		it('does not merge from stdClass to stdClass fields without setters', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new stdClass();
			$source->{$fieldName1} = $from1;
			$source->{$fieldName2} = $from2;
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($oldTo1);
			expect($destination->{$fieldName2})->toBe($oldTo2);
		});
		it('does not merge from ArrayAccess to stdClass fields without setters', function ()
		{
			$oldTo1 = 'c';
			$oldTo2 = 'd';

			$strategy1 = mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = mock(DT\Strategy\StrategyInterface::class);

			$strategy1->shouldReceive('merge')->never();
			$strategy2->shouldReceive('merge')->never();

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = mock(ArrayAccess::class);
			$source->shouldNotReceive('offsetExists')->with($fieldName1);
			$source->shouldNotReceive('offsetExists')->with($fieldName2);
			$source->shouldNotReceive('offsetGet')->with($fieldName1);
			$source->shouldNotReceive('offsetGet')->with($fieldName2);
			$destination = new stdClass();
			$destination->{$fieldName1} = $oldTo1;
			$destination->{$fieldName2} = $oldTo2;
			$fields = [
				[$fieldName1, ['test1', false], null, $strategy1],
				[$fieldName2, ['getTest2', true], null, $strategy2],
			];

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			$strategy->merge($source, $destination);
			expect($destination->{$fieldName1})->toBe($oldTo1);
			expect($destination->{$fieldName2})->toBe($oldTo2);
		});
		it('throws on source that is not map', function ()
		{
			$source = mock();
			$destination = [];
			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, [], false);
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
					sprintf('Merge can be done only for key-value map, not %s', get_class($source))
				);
			}
		});
		it('throws on destination that is not map', function ()
		{
			$source = [];
			$destination = mock();
			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, [], false);
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
					sprintf('Merge can be done only into key-value map, not %s', get_class($destination))
				);
			}
		});
		it('rethrows wrapped invalid data exception', function ()
		{
			$from1 = 'a';
			$oldTo1 = 'b';

			$violations = ['test' => 123];
			$innerError = new DT\Exception\InvalidData($violations);

			$fieldStrategy = mock(DT\Strategy\StrategyInterface::class);
			$fieldStrategy->shouldReceive('merge')->with($from1, $oldTo1)->once()->andThrow($innerError);

			$fieldName1 = 'test_1';
			$source = [$fieldName1 => $from1];
			$destination = [$fieldName1 => $oldTo1];
			$fields = [
				[$fieldName1, ['test1', false], ['test1', false], $fieldStrategy],
			];
			$error = new DT\Exception\InvalidData([DT\Validator\FieldData::INVALID_INNER => [$fieldName1 => $violations]], $innerError);

			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, $fields, false);
			expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->merge($source, $destination);
			})->toThrow($error);
		});

	});
});
