<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;

\describe(DT\Strategy\FieldData::class, function ()
{
	\describe('->hydrate', function ()
	{
		\it('hydrates array to object', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = \mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = \mock(DT\Strategy\StrategyInterface::class);

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
			);
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
			);

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
			\expect($destination->test1)->toBe($newTo1);
			\expect($destination->getTest2())->toBe($newTo2);
		});
		\it('hydrates stdClass to object', function ()
		{
			$from1 = 'a';
			$from2 = 'b';
			$oldTo1 = 'c';
			$oldTo2 = 'd';
			$newTo1 = 'e';
			$newTo2 = 'f';

			$strategy1 = \mock(DT\Strategy\StrategyInterface::class);
			$strategy2 = \mock(DT\Strategy\StrategyInterface::class);

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
			);
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
			);

			$fieldName1 = 'test_1';
			$fieldName2 = 'test_2';
			$source = new \stdClass();
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
			\expect($destination->test1)->toBe($newTo1);
			\expect($destination->getTest2())->toBe($newTo2);
		});
		\it('throws on source that is not map', function ()
		{
			$source = \mock();
			$destination = new Example\DTO\Data();
			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, [], false);
			$error = new \LogicException(\sprintf('Hydration can be done only from key-value map, not %s.', \get_class($source)));
			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($error);
		});
		\it('throws on destination of invalid type', function ()
		{
			$source = [];
			$destination = \mock();
			$strategy = new DT\Strategy\FieldData(Example\DTO\Data::class, [], false);
			$error = new \LogicException(\sprintf('Hydration can be done only to %s, not %s', Example\DTO\Data::class, \get_class($destination)));
			\expect(function () use (&$strategy, &$source, &$destination)
			{
				$strategy->hydrate($source, $destination);
			})->toThrow($error);
		});
	});
});
