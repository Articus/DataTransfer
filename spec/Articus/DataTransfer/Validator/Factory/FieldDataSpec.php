<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

/**
 * TODO add example to test default option values
 */
class FieldDataSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\FieldMetadataProviderInterface $metadataProvider,
		DT\Validator\PluginManager $validatorManager,
		DT\Validator\ValidatorInterface $validator
	)
	{
		$type = 'test\\Class';
		$subset = 'testSubset';
		$fieldName = 'testField';
		$validatorDeclaration = ['testValidator', ['test' => 123]];
		$options = ['type' => $type, 'subset' => $subset];

		$container->get(DT\FieldMetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(DT\Validator\PluginManager::class)->shouldBeCalledOnce()->willReturn($validatorManager);
		$metadataProvider->getClassFields($type, $subset)->shouldBeCalledOnce()->willYield([[$fieldName, null, null]]);
		$metadataProvider->getFieldValidator($type, $subset, $fieldName)->shouldBeCalledOnce()->willReturn($validatorDeclaration);
		$validatorManager->get(...$validatorDeclaration)->shouldBeCalledOnce()->willReturn($validator);

		$this->__invoke($container, 'testName', $options)->shouldBeAnInstanceOf(DT\Validator\FieldData::class);
		//TODO check that constructor received expected arguments
	}

	public function it_throws_on_no_type(ContainerInterface $container)
	{
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'testName', []]);
	}
}
