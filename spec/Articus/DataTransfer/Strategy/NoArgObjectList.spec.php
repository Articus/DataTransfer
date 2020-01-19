<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;

\describe(DT\Strategy\NoArgObjectList::class, function ()
{
	\describe('->hydrate', function ()
	{
		\it('hydrates from null', function ()
		{
			$source = null;
			$destination = 'test';
			$originalDestination = $destination;
			$typeStrategy = \mock(DT\Strategy\StrategyInterface::class);

			$strategy = new DT\Strategy\NoArgObjectList($typeStrategy, Example\DTO\Data::class);
			$strategy->hydrate($source, $destination);
			\expect($destination)->toBe($originalDestination);
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
			);

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
	});
});
