<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use LogicException;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use spec\Example;

/**
 * TODO add example to test default option values
 */
class TypeCompliantSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $validatorManager,
		DT\Validator\ValidatorInterface $validator
	)
	{
		$type = Example\DTO\Data::class;
		$subset = 'testSubset';
		$validatorDeclaration = ['testValidator', ['test' => 123]];
		$options = ['type' => $type, 'subset' => $subset];

		$container->get(DT\ClassMetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($validatorManager);
		$metadataProvider->getClassValidator($type, $subset)->shouldBeCalledOnce()->willReturn($validatorDeclaration);
		$validatorManager->__invoke(...$validatorDeclaration)->shouldBeCalledOnce()->willReturn($validator);

		$service = $this->__invoke($container, 'testName', $options);
		$service->shouldBeAnInstanceOf(DT\Validator\TypeCompliant::class);
		$service->shouldHaveProperty('typeValidator', $validator);
	}

	public function it_throws_on_type_that_does_not_exist(ContainerInterface $container)
	{
		$this->shouldThrow(LogicException::class)->during('__invoke', [$container, 'testName', ['type' => 'unknown']]);
	}
}
