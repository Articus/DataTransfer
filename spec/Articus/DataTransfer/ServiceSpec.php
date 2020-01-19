<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class ServiceSpec extends ObjectBehavior
{
	public function it_returns_strategy_for_typed_data(
		DT\ClassMetadataProviderInterface $metadataProvider,
		DT\Strategy\PluginManager $strategyManager,
		DT\Validator\PluginManager $validatorManager,
		DT\Strategy\HydratorInterface $untypedDataHydrator,
		$typedData,
		DT\Strategy\StrategyInterface $strategy
	)
	{
		$strategyDeclaration = ['qwer', ['asdf' => 123]];
		$subset = 'test';

		$metadataProvider->getClassStrategy(\get_class($typedData->getWrappedObject()), $subset)->shouldBeCalledOnce()->willReturn($strategyDeclaration);
		$strategyManager->get(...$strategyDeclaration)->shouldBeCalledOnce()->willReturn($strategy);

		$this->beConstructedWith($metadataProvider, $strategyManager, $validatorManager, $untypedDataHydrator);
		$this->getTypedDataStrategy($typedData, $subset)->shouldBe($strategy);
	}

	public function it_throws_when_data_for_strategy_is_not_object(
		DT\ClassMetadataProviderInterface $metadataProvider,
		DT\Strategy\PluginManager $strategyManager,
		DT\Validator\PluginManager $validatorManager,
		DT\Strategy\HydratorInterface $untypedDataHydrator
	)
	{
		$typedData = 1;
		$subset = 'test';

		$this->beConstructedWith($metadataProvider, $strategyManager, $validatorManager, $untypedDataHydrator);
		$this->shouldThrow(\LogicException::class)->during('getTypedDataStrategy', [$typedData, $subset]);
	}

	public function it_returns_validator_for_typed_data(
		DT\ClassMetadataProviderInterface $metadataProvider,
		DT\Strategy\PluginManager $strategyManager,
		DT\Validator\PluginManager $validatorManager,
		DT\Strategy\HydratorInterface $untypedDataHydrator,
		$typedData,
		DT\Validator\ValidatorInterface $validator
	)
	{
		$validatorDeclaration = ['qwer', ['asdf' => 123]];
		$subset = 'test';

		$metadataProvider->getClassValidator(\get_class($typedData->getWrappedObject()), $subset)->shouldBeCalledOnce()->willReturn($validatorDeclaration);
		$validatorManager->get(...$validatorDeclaration)->shouldBeCalledOnce()->willReturn($validator);

		$this->beConstructedWith($metadataProvider, $strategyManager, $validatorManager, $untypedDataHydrator);
		$this->getTypedDataValidator($typedData, $subset)->shouldBe($validator);
	}

	public function it_throws_when_data_for_validator_is_not_object(
		DT\ClassMetadataProviderInterface $metadataProvider,
		DT\Strategy\PluginManager $strategyManager,
		DT\Validator\PluginManager $validatorManager,
		DT\Strategy\HydratorInterface $untypedDataHydrator
	)
	{
		$typedData = 1;
		$subset = 'test';

		$this->beConstructedWith($metadataProvider, $strategyManager, $validatorManager, $untypedDataHydrator);
		$this->shouldThrow(\LogicException::class)->during('getTypedDataValidator', [$typedData, $subset]);
	}
}
