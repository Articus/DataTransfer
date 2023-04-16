<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;
use spec\Example;

describe(DT\Strategy\Factory\NoArgObjectList::class, function ()
{
	afterEach(function ()
	{
		Mockery::close();
	});
	describe('->hydrate of service created via ->__invoke', function ()
	{
		it('creates new array if destination is null', function ()
		{
			$source = [];
			$destination = null;
			$newDestination = [];

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

			$factory = new DT\Strategy\Factory\NoArgObjectList();
			$service = $factory($container, 'testService', $options);
			expect($service)->toBeAnInstanceOf(DT\Strategy\IdentifiableValue::class);
			if ($service instanceof DT\Strategy\IdentifiableValue)
			{
				$service->hydrate($source, $destination);
				expect($destination)->toBe($newDestination);
			}
		});
		it('creates new array items in destination', function ()
		{
			$source = [mock()];
			$destination = [];
			$newDestinationItem = mock();
			$newDestination = [&$newDestinationItem];

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
				function ($a, &$b) use (&$source, $className, &$newDestinationItem)
				{
					$result = ($a === $source[0]) && ($b instanceof $className);
					if ($result)
					{
						$b = $newDestinationItem;
					}
					return $result;
				}
			)->once();

			$factory = new DT\Strategy\Factory\NoArgObjectList();
			$service = $factory($container, 'testService', $options);
			expect($service)->toBeAnInstanceOf(DT\Strategy\IdentifiableValue::class);
			if ($service instanceof DT\Strategy\IdentifiableValue)
			{
				$service->hydrate($source, $destination);
				expect($destination)->toBe($newDestination);
			}
		});
		it('removes all existing array items in destination', function ()
		{
			$source = [];
			$destinationItem = mock();
			$destination = [$destinationItem];
			$newDestination = [];

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

			$factory = new DT\Strategy\Factory\NoArgObjectList();
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
		it('creates new array if destination is null', function ()
		{
			$source = [];
			$destination = null;
			$newDestination = [];

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

			$factory = new DT\Strategy\Factory\NoArgObjectList();
			$service = $factory($container, 'testService', $options);
			expect($service)->toBeAnInstanceOf(DT\Strategy\IdentifiableValue::class);
			if ($service instanceof DT\Strategy\IdentifiableValue)
			{
				$service->merge($source, $destination);
				expect($destination)->toBe($newDestination);
			}
		});
		it('creates new array items in destination', function ()
		{
			$source = [mock()];
			$destination = [];
			$defaultDestinationItem = mock();
			$newDestinationItem = mock();
			$newDestination = [&$newDestinationItem];

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
			)->andReturn($defaultDestinationItem)->once();
			$strategy->shouldReceive('merge')->withArgs(
				function ($a, &$b) use (&$source, &$defaultDestinationItem, &$newDestinationItem)
				{
					$result = ($a === $source[0]) && ($b === $defaultDestinationItem);
					if ($result)
					{
						$b = $newDestinationItem;
					}
					return $result;
				}
			)->once();

			$factory = new DT\Strategy\Factory\NoArgObjectList();
			$service = $factory($container, 'testService', $options);
			expect($service)->toBeAnInstanceOf(DT\Strategy\IdentifiableValue::class);
			if ($service instanceof DT\Strategy\IdentifiableValue)
			{
				$service->merge($source, $destination);
				expect($destination)->toBe($newDestination);
			}
		});
		it('removes all existing array items in destination', function ()
		{
			$source = [];
			$destinationItem = mock();
			$destination = [$destinationItem];
			$newDestination = [];

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

			$factory = new DT\Strategy\Factory\NoArgObjectList();
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
