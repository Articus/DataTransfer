<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

/**
 * TODO add example to test default option values
 */
class TypeCompliantSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		DT\Validator\PluginManager $validatorManager,
		DT\Validator\ValidatorInterface $validator
	)
	{
		$type = 'test\\Class';
		$subset = 'testSubset';
		$validatorDeclaration = ['testValidator', ['test' => 123]];
		$options = ['type' => $type, 'subset' => $subset];

		$container->get(DT\ClassMetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(DT\Validator\PluginManager::class)->shouldBeCalledOnce()->willReturn($validatorManager);
		$metadataProvider->getClassValidator($type, $subset)->shouldBeCalledOnce()->willReturn($validatorDeclaration);
		$validatorManager->get(...$validatorDeclaration)->shouldBeCalledOnce()->willReturn($validator);

		$service = $this->__invoke($container, 'testName', $options);
		$service->shouldBeAnInstanceOf(DT\Validator\TypeCompliant::class);
		$service->shouldHaveProperty('typeValidator', $validator);
	}

	public function it_throws_on_no_type(ContainerInterface $container)
	{
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'testName', []]);
	}
}
