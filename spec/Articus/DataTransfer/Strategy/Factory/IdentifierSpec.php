<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

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
		$service->shouldBeAnInstanceOf(DT\Strategy\Identifier::class);
		$service->shouldHaveProperty('loader', $loader);
		$service->shouldHaveProperty('type', $type);
	}
}
