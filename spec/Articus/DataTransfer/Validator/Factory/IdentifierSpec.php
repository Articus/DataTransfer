<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator\Factory;

use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Articus\DataTransfer as DT;

class IdentifierSpec extends ObjectBehavior
{
	public function it_creates_service(
		ContainerInterface $container,
		DT\IdentifiableValueLoader $loader
	)
	{
		$type = 'testType';
		$options = ['type' => $type];

		$container->get(DT\IdentifiableValueLoader::class)->willReturn($loader);

		$service = $this->__invoke($container, 'testName', $options);
		$service->shouldBeAnInstanceOf(DT\Validator\Identifier::class);
		$service->shouldHaveProperty('loader', $loader);
		$service->shouldHaveProperty('type', $type);
	}

	public function it_throws_on_no_type(ContainerInterface $container)
	{
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'testName']);
	}
}
