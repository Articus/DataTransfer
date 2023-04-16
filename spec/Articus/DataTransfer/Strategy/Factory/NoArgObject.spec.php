<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;
use spec\Example;

describe(DT\Strategy\Factory\NoArgObject::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	describe('->hydrate of service created via ->__invoke', function ()
	{
		it('creates new object of specified type if destination is null', function ()
		{
			$source = mock();
			$destination = null;
			$newDestination = mock();

			$className = Example\DTO\Data::class;
			$subset = 'testSubset';
			$options = [
				'type' => $className,
				'subset' => $subset,
			];
			$strategyDeclaration = ['testStrategy', ['test' => 123]];
			$container = mock(ContainerInterface::class);
			$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = mock(PluginManagerInterface::class);
			$strategy = mock(DT\Strategy\StrategyInterface::class);

			$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();
			$metadataProvider->shouldReceive('getClassStrategy')->with($className, $subset)->andReturn($strategyDeclaration)->once();
			$strategyManager->shouldReceive('__invoke')->with(...$strategyDeclaration)->andReturn($strategy)->once();
			$strategy->shouldReceive('hydrate')->withArgs(
				function ($a, &$b) use (&$source, $className, &$newDestination)
				{
					$result = ($a === $source) && ($b instanceof $className);
					if ($result)
					{
						$b = $newDestination;
					}
					return $result;
				}
			)->once();

			$factory = new DT\Strategy\Factory\NoArgObject();
			$service = $factory($container, 'testService', $options);
			expect($service)->toBeAnInstanceOf(DT\Strategy\IdentifiableValue::class);
			if ($service instanceof DT\Strategy\IdentifiableValue)
			{
				$service->hydrate($source, $destination);
				expect($destination)->toBe($newDestination);
			}
		});
	});
	describe('->merge of service created via ->__invoke', function ()
	{
		it('creates new object of specified type and extracts from it if destination is null', function ()
		{
			$source = mock();
			$destination = null;
			$defaultDestination = mock();
			$newDestination = mock();

			$className = Example\DTO\Data::class;
			$subset = 'testSubset';
			$options = [
				'type' => $className,
				'subset' => $subset,
			];
			$strategyDeclaration = ['testStrategy', ['test' => 123]];
			$container = mock(ContainerInterface::class);
			$metadataProvider = mock(DT\ClassMetadataProviderInterface::class);
			$strategyManager = mock(PluginManagerInterface::class);
			$strategy = mock(DT\Strategy\StrategyInterface::class);

			$container->shouldReceive('get')->with(DT\ClassMetadataProviderInterface::class)->andReturn($metadataProvider)->once();
			$container->shouldReceive('get')->with(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->andReturn($strategyManager)->once();
			$metadataProvider->shouldReceive('getClassStrategy')->with($className, $subset)->andReturn($strategyDeclaration)->once();
			$strategyManager->shouldReceive('__invoke')->with(...$strategyDeclaration)->andReturn($strategy)->once();
			$strategy->shouldReceive('extract')->withArgs(
				function ($a) use ($className)
				{
					return ($a instanceof $className);
				}
			)->andReturn($defaultDestination)->once();
			$strategy->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$source, &$defaultDestination, &$newDestination)
				{
					$result = ($a === $source) && ($b === $defaultDestination);
					if ($result)
					{
						$b = $newDestination;
					}
					return $result;
				}
			)->once();

			$factory = new DT\Strategy\Factory\NoArgObject();
			$service = $factory($container, 'testService', $options);
			expect($service)->toBeAnInstanceOf(DT\Strategy\IdentifiableValue::class);
			if ($service instanceof DT\Strategy\IdentifiableValue)
			{
				$service->merge($source, $destination);
				expect($destination)->toBe($newDestination);
			}
		});
	});
});
