<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;

describe(DT\Strategy\Whatever::class, function ()
{
	it('extracts by returning source', function ()
	{
		$source = mock();
		$strategy = new DT\Strategy\Whatever();
		expect($strategy->extract($source))->toBe($source);
	});
	it('hydrates by coping source to destination', function ()
	{
		$source = mock();
		$destination = mock();

		$strategy = new DT\Strategy\Whatever();
		$strategy->hydrate($source, $destination);
		expect($destination)->toBe($source);
	});
	it('merges by coping source to destination', function ()
	{
		$source = mock();
		$destination = mock();

		$strategy = new DT\Strategy\Whatever();
		$strategy->merge($source, $destination);
		expect($destination)->toBe($source);
	});
});
