<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

/**
 * TODO add example to test default option values
 */
class CollectionSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		PluginManagerInterface $validatorManager,
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
			new DT\Validator\Options\ChainLink(['testValidator1', ['test1' => 111], true]),
			new DT\Validator\Options\ChainLink(['testValidator2', [], false]),
		];
		$container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($validatorManager);
		$validatorManager->__invoke(DT\Validator\Chain::class, ['links' => $links])->shouldBeCalledOnce()->willReturn($itemValidator);

		$service = $this->__invoke($container, 'testName', $options);
		$service->shouldBeAnInstanceOf(DT\Validator\Collection::class);
		$service->shouldHaveProperty('itemValidator', $itemValidator);
	}
}
