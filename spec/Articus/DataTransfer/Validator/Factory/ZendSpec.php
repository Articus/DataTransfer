<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Zend\Validator\ValidatorInterface as ZendValidator;
use Zend\Validator\ValidatorPluginManager;

class ZendSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		ValidatorPluginManager $validatorPluginManager,
		ZendValidator $validator
	)
	{
		$name = 'testName';
		$options = ['testOptions' => 123];
		$container->get(ValidatorPluginManager::class)->shouldBeCalledOnce()->willReturn($validatorPluginManager);
		$validatorPluginManager->get($name, $options)->shouldBeCalledOnce()->willReturn($validator);
		$this->__invoke($container, $name, $options)->shouldBeAnInstanceOf(DT\Validator\Zend::class);
		//TODO check that constructor received expected arguments
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
