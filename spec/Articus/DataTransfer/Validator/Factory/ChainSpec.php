<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class ChainSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		PluginManagerInterface $validatorManager,
		DT\Validator\ValidatorInterface $validator1,
		DT\Validator\ValidatorInterface $validator2
	)
	{
		$link1 = ['testValidator1', ['testOption1' => 1], true];
		$link2 = ['testValidator2', ['testOption2' => 2], false];
		$options = ['links' => [$link1, $link2]];
		$container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($validatorManager);
		$validatorManager->__invoke($link1[0], $link1[1])->shouldBeCalledOnce()->willReturn($validator1);
		$validatorManager->__invoke($link2[0], $link2[1])->shouldBeCalledOnce()->willReturn($validator2);

		$service = $this->__invoke($container, 'testName', $options);
		$service->shouldBeAnInstanceOf(DT\Validator\Chain::class);
		$service->shouldHaveProperty('links', [[$validator1, $link1[2]], [$validator2, $link2[2]]]);
	}
}
