<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Laminas\Validator\ValidatorInterface as LaminasValidator;
use Laminas\Validator\ValidatorPluginManager;

class LaminasSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		ValidatorPluginManager $validatorPluginManager,
		LaminasValidator $validator
	)
	{
		$name = 'testName';
		$options = ['testOptions' => 123];
		$container->get(ValidatorPluginManager::class)->shouldBeCalledOnce()->willReturn($validatorPluginManager);
		$validatorPluginManager->get($name, $options)->shouldBeCalledOnce()->willReturn($validator);

		$service = $this->__invoke($container, $name, $options);
		$service->shouldBeAnInstanceOf(DT\Validator\Laminas::class);
		$service->shouldHaveProperty('laminasValidator', $validator);
	}

	public function it_can_create_what_zend_validator_plugin_manager_can_create(
		ContainerInterface $container,
		ValidatorPluginManager $validatorPluginManager
	)
	{
		$name = 'testName';
		$inValidatorPluginManager = true;
		$container->get(ValidatorPluginManager::class)->shouldBeCalledOnce()->willReturn($validatorPluginManager);
		$validatorPluginManager->has($name)->shouldBeCalledOnce()->willReturn($inValidatorPluginManager);

		$this->canCreate($container, $name)->shouldBe($inValidatorPluginManager);
	}
}
