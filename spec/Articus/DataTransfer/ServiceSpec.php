<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer;

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use PhpSpec\ObjectBehavior;
use function get_class;

class ServiceSpec extends ObjectBehavior
{
	public function it_returns_strategy_for_typed_data(
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager,
		$typedData,
		DT\Strategy\StrategyInterface $strategy
	)
	{
		$strategyDeclaration = ['qwer', ['asdf' => 123]];
		$subset = 'test';

		$metadataProvider->getClassStrategy(get_class($typedData->getWrappedObject()), $subset)->shouldBeCalledOnce()->willReturn($strategyDeclaration);
		$strategyManager->__invoke(...$strategyDeclaration)->shouldBeCalledOnce()->willReturn($strategy);

		$this->beConstructedWith($metadataProvider, $strategyManager, $validatorManager);
		$this->getTypedDataStrategy($typedData, $subset)->shouldBe($strategy);
	}

	public function it_returns_validator_for_typed_data(
		DT\ClassMetadataProviderInterface $metadataProvider,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager,
		$typedData,
		DT\Validator\ValidatorInterface $validator
	)
	{
		$validatorDeclaration = ['qwer', ['asdf' => 123]];
		$subset = 'test';

		$metadataProvider->getClassValidator(get_class($typedData->getWrappedObject()), $subset)->shouldBeCalledOnce()->willReturn($validatorDeclaration);
		$validatorManager->__invoke(...$validatorDeclaration)->shouldBeCalledOnce()->willReturn($validator);

		$this->beConstructedWith($metadataProvider, $strategyManager, $validatorManager);
		$this->getTypedDataValidator($typedData, $subset)->shouldBe($validator);
	}
}
