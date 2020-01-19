<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy\Factory;

use spec\Example;
use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

/**
 * TODO add example to test default option values
 */
class NoArgObjectListSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		DT\Strategy\PluginManager $strategyManager,
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
		$container->get(DT\Strategy\PluginManager::class)->willReturn($strategyManager);
		$metadataProvider->getClassStrategy($className, $subset)->willReturn($strategyDeclaration);
		$strategyManager->get(...$strategyDeclaration)->willReturn($strategy);

		$service = $this->__invoke($container, 'testName', $options);
		$service->shouldBeAnInstanceOf(DT\Strategy\NoArgObjectList::class);
		//TODO check that constructor received expected arguments
	}

	public function it_throws_on_no_type(ContainerInterface $container)
	{
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'testName']);
	}

	public function it_throws_on_type_that_does_not_exist(ContainerInterface $container)
	{
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'testName', ['type' => 'unknown']]);
	}
}
