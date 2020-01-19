<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;

\describe(DT\Strategy\UntypedData::class, function ()
{
	\describe('->hydrate', function ()
	{
		\it('replaces scalar with scalar', function ()
		{
			$source = 'a';
			$destination = 'b';

			$hydrator = new DT\Strategy\UntypedData();
			$hydrator->hydrate($source, $destination);
			\expect($destination)->toBe($source);
		});
		\it('replaces indexed array with indexed array', function ()
		{
			$source = [1,2];
			$destination = [3,4];

			$hydrator = new DT\Strategy\UntypedData();
			$hydrator->hydrate($source, $destination);
			\expect($destination)->toBe($source);
		});
		\it('combines associative array with associative array', function ()
		{
			$source = ['a' => 1, 'b' => 2];
			$destination = ['b' => 3, 'c' => 4];
			$newDestination = ['b' => 2, 'c' => 4, 'a' => 1];

			$hydrator = new DT\Strategy\UntypedData();
			$hydrator->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('combines std class with std class', function ()
		{
			$source = new \stdClass();
			$source->a = 1;
			$source->b = 2;
			$destination = new \stdClass();
			$destination->b = 3;
			$destination->c = 4;
			$destinationReference = &$destination;
			$newDestination = new \stdClass();
			$newDestination->b = 2;
			$newDestination->c = 4;
			$newDestination->a = 1;

			$hydrator = new DT\Strategy\UntypedData();
			$hydrator->hydrate($source, $destination);
			\expect($destination)->toEqual($newDestination);
			\expect($destination)->toBe($destinationReference);
		});
		\it('combines associative array with inner associative array recursively', function ()
		{
			$source = ['test' => ['a' => 1, 'b' => 2]];
			$destination = ['test' => ['b' => 3, 'c' => 4]];
			$newDestination = ['test' => ['b' => 2, 'c' => 4, 'a' => 1]];

			$hydrator = new DT\Strategy\UntypedData();
			$hydrator->hydrate($source, $destination);
			\expect($destination)->toBe($newDestination);
		});
		\it('combines std class with std class recursively', function ()
		{
			$sourceItem = new \stdClass();
			$sourceItem->a = 1;
			$sourceItem->b = 2;
			$source = new \stdClass();
			$source->test = $sourceItem;

			$destinationItem = new \stdClass();
			$destinationItem->b = 3;
			$destinationItem->c = 4;
			$destination = new \stdClass();
			$destination->test = $destinationItem;

			$destinationItemReference = &$destinationItem;
			$destinationReference = &$destination;

			$newDestinationItem = new \stdClass();
			$newDestinationItem->b = 2;
			$newDestinationItem->c = 4;
			$newDestinationItem->a = 1;
			$newDestination = new \stdClass();
			$newDestination->test = $newDestinationItem;

			$hydrator = new DT\Strategy\UntypedData();
			$hydrator->hydrate($sourceItem, $destinationItem);
			\expect($destinationItem)->toEqual($newDestinationItem);
			\expect($destination)->toEqual($newDestination);
			\expect($destinationItem)->toBe($destinationItemReference);
			\expect($destination)->toBe($destinationReference);
		});
	});
});
