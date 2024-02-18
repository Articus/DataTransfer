<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Psr\SimpleCache\CacheInterface;
use spec\Example;

describe(DT\MetadataCache\Psr16::class, function ()
{
	it('delegates getting', function ()
	{
		$className = Example\DTO\Data::class;
		$key = 'spec.Example.DTO.Data';
		$value = ['test' => 123];

		$psr16Cache = mock(CacheInterface::class);
		$psr16Cache->shouldReceive('get')->with($key)->andReturn($value)->once();

		$cache = new DT\MetadataCache\Psr16($psr16Cache);
		expect($cache->get($className))->toBe($value);
	});
	it('delegates setting', function ()
	{
		$className = Example\DTO\Data::class;
		$key = 'spec.Example.DTO.Data';
		$hitValue = ['test-hit' => 111];
		$missValue = ['test-miss' => 222];

		$psr16Cache = mock(CacheInterface::class);
		$psr16Cache->shouldReceive('set')->with($key, $hitValue)->andReturn(true)->once();
		$psr16Cache->shouldReceive('set')->with($key, $missValue)->andReturn(false)->once();

		$cache = new DT\MetadataCache\Psr16($psr16Cache);
		expect($cache->set($className, $hitValue))->toBe(true);
		expect($cache->set($className, $missValue))->toBe(false);
	});
});
