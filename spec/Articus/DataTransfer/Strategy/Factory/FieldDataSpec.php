<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy\Factory;

use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use spec\Example;
use Articus\DataTransfer as DT;

/**
 * TODO add example to test default option values
 */
class FieldDataSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\FieldMetadataProviderInterface $metadataProvider,
		DT\Strategy\PluginManager $strategyManager,
		DT\Strategy\StrategyInterface $strategy
	)
	{
		$className = Example\DTO\Data::class;
		$subset = 'testSubset';
		$extractStdClass = true;
		$options = [
			'type' => $className,
			'subset' => $subset,
			'extract_std_class' => $extractStdClass,
		];
		$fieldName = 'testField';
		$fieldGetter = ['testGetter', true];
		$fieldSetter = ['testSetter', true];
		$strategyDeclaration = ['testStrategy', ['test' => 123]];
		$container->get(DT\FieldMetadataProviderInterface::class)->willReturn($metadataProvider);
		$container->get(DT\Strategy\PluginManager::class)->willReturn($strategyManager);
		$metadataProvider->getClassFields($className, $subset)->willReturn([[$fieldName, $fieldGetter, $fieldSetter]]);
		$metadataProvider->getFieldStrategy($className, $subset, $fieldName)->willReturn($strategyDeclaration);
		$strategyManager->get(...$strategyDeclaration)->willReturn($strategy);

		$service = $this->__invoke($container, 'testName', $options);
		$service->shouldBeAnInstanceOf(DT\Strategy\FieldData::class);
		$service->shouldHaveProperty('type', $className);
		$service->shouldHaveProperty('typeFields', [[$fieldName, $fieldGetter, $fieldSetter, $strategy]]);
		$service->shouldHaveProperty('extractStdClass', $extractStdClass);
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
