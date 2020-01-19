<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

class PluginManagerSpec extends ObjectBehavior
{
	public function it_creates_service(ContainerInterface $container)
	{
		$container->get('config')->willReturn([]);
		$this->__invoke($container, 'testName')->shouldBeAnInstanceOf(DT\Strategy\PluginManager::class);
		//TODO check that constructor received expected arguments
	}
}
