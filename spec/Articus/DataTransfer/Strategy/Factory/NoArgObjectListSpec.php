<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use LogicException;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use spec\Example;

/**
 * TODO add example to test default option values
 */
class NoArgObjectListSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		DT\Strategy\StrategyInterface $strategy
	)
	{
		$className = Example\DTO\Data::class;
		$subset = 'testSubset';
		$options = [
			'type' => $className,
			'subset' => $subset,
		];
		$strategyDeclaration = ['testStrategy', ['test' => 123]];
		$container->get(DT\ClassMetadataProviderInterface::class)->willReturn($metadataProvider);
		$container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->willReturn($strategyManager);
		$metadataProvider->getClassStrategy($className, $subset)->willReturn($strategyDeclaration);
		$strategyManager->__invoke(...$strategyDeclaration)->willReturn($strategy);

		$service = $this->__invoke($container, 'testName', $options);
		$service->shouldBeAnInstanceOf(DT\Strategy\IdentifiableValue::class);
		$service->shouldHavePropertyOfType('valueStrategy', DT\Strategy\IdentifiableValueList::class);
	}

	public function it_throws_on_type_that_does_not_exist(ContainerInterface $container)
	{
		$this->shouldThrow(LogicException::class)->during('__invoke', [$container, 'testName', ['type' => 'unknown']]);
	}
}
