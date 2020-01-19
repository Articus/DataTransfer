<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

/**
 * TODO add example to test default option values
 */
class CollectionSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\Validator\PluginManager $validatorManager,
		DT\Validator\ValidatorInterface $itemValidator
	)
	{
		$options = [
			'validators' => [
				['name' => 'testValidator1', 'options' => ['test1' => 111], 'blocker' => true],
				['name' => 'testValidator2'],
			]
		];
		$links = [
			['testValidator1', ['test1' => 111], true],
			['testValidator2', null, false],
		];
		$container->get(DT\Validator\PluginManager::class)->shouldBeCalledOnce()->willReturn($validatorManager);
		$validatorManager->get(DT\Validator\Chain::class, ['links' => $links])->shouldBeCalledOnce()->willReturn($itemValidator);

		$this->__invoke($container, 'testName', $options)->shouldBeAnInstanceOf(DT\Validator\Collection::class);
		//TODO check that constructor received expected arguments
	}

	public function it_throws_if_there_is_no_name_for_validator_in_options(ContainerInterface $container)
	{
		$options = ['validators' => [[]]];
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'testName', $options]);
	}
}
