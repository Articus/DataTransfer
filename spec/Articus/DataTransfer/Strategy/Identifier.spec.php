<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;

describe(DT\Strategy\Identifier::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	describe('->hydrate', function ()
	{
		it('hydrates from null', function ()
		{
			$loader = mock(DT\IdentifiableValueLoader::class);
			$type = 'test';
			$source = null;
			$destination = mock();

			$strategy = new DT\Strategy\Identifier($loader, $type);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBeNull();
		});
		it('hydrates from identifier to null', function ()
		{
			$loader = mock(DT\IdentifiableValueLoader::class);
			$type = 'test';
			$source = 123;
			$destination = null;
			$newDestination = mock();

			$loader->shouldReceive('get')->with($type, $source)->andReturn($newDestination)->once();

			$strategy = new DT\Strategy\Identifier($loader, $type);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBe($newDestination);
		});
		it('hydrates from identifier to value with same identifier', function ()
		{
			$loader = mock(DT\IdentifiableValueLoader::class);
			$type = 'test';
			$source = 123;
			$destination = mock();
			$newDestination = $destination;

			$loader->shouldReceive('identify')->with($type, $destination)->andReturn($source)->once();

			$strategy = new DT\Strategy\Identifier($loader, $type);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBe($newDestination);
		});
		it('hydrates from identifier to value with different identifier', function ()
		{
			$loader = mock(DT\IdentifiableValueLoader::class);
			$type = 'test';
			$source = 123;
			$destination = mock();
			$newDestination = mock();

			$loader->shouldReceive('identify')->with($type, $destination)->andReturn(456)->once();
			$loader->shouldReceive('get')->with($type, $source)->andReturn($newDestination);

			$strategy = new DT\Strategy\Identifier($loader, $type);
			$strategy->hydrate($source, $destination);
			expect($destination)->toBe($newDestination);
		});
	});
	describe('->merge', function ()
	{
		it('merges from null', function ()
		{
			$loader = mock(DT\IdentifiableValueLoader::class);
			$type = 'test';
			$source = null;
			$destination = 456;

			$strategy = new DT\Strategy\Identifier($loader, $type);
			$strategy->merge($source, $destination);
			expect($destination)->toBeNull();
		});
		it('merges from integer', function ()
		{
			$loader = mock(DT\IdentifiableValueLoader::class);
			$type = 'test';
			$source = 123;
			$destination = null;

			$loader->shouldReceive('wish')->with($type, $source)->once();

			$strategy = new DT\Strategy\Identifier($loader, $type);
			$strategy->merge($source, $destination);
			expect($destination)->toBe($source);
		});
		it('merges from string', function ()
		{
			$loader = mock(DT\IdentifiableValueLoader::class);
			$type = 'test';
			$source = 'abc';
			$destination = null;

			$loader->shouldReceive('wish')->with($type, $source)->once();

			$strategy = new DT\Strategy\Identifier($loader, $type);
			$strategy->merge($source, $destination);
			expect($destination)->toBe($source);
		});
	});
});
