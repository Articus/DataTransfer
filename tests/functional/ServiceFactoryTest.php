<?php
namespace Test\DataTransfer;


use Articus\DataTransfer\Service;
use Articus\DataTransfer\ServiceFactory;
use Articus\DataTransfer\Strategy;
use Interop\Container\ContainerInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\Validator\ValidatorPluginManager;

class ServiceFactoryTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Test\DataTransfer\FunctionalTester
	 */
	protected $tester;

	public function testServiceIsCreatedFromSimpleConfiguration()
	{
		$config = [
			'data_transfer' => [
				'metadata_cache' => [
    				'adapter' => 'blackhole',
				],
			],
		];
		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get('config')->willReturn($config);
		$container = $containerProphecy->reveal();

		$factory = new ServiceFactory();
		$service = $factory($container, Service::class);
		$this->tester->assertInstanceOf(Service::class, $service);
	}

	public function testServiceIsCreatedFromExternalConfiguration()
	{
		$config = [
			'data_transfer' => [
				'metadata_cache' => 'MetadataCacheStorage',
				'strategies' => 'StrategyPluginManager',
				'validators' => 'ValidatorPluginManager',
			],
		];
		$containerProphecy = $this->prophesize(ContainerInterface::class);

		$containerProphecy->get('config')->willReturn($config);

		$containerProphecy->get('MetadataCacheStorage')
			->willReturn($this->prophesize(StorageInterface::class)->reveal());
		$containerProphecy->has('MetadataCacheStorage')->willReturn(true);

		$containerProphecy->get('StrategyPluginManager')
			->willReturn($this->prophesize(Strategy\PluginManager::class)->reveal());
		$containerProphecy->has('StrategyPluginManager')->willReturn(true);

		$containerProphecy->get('ValidatorPluginManager')
			->willReturn($this->prophesize(ValidatorPluginManager::class)->reveal());
		$containerProphecy->has('ValidatorPluginManager')->willReturn(true);

		$container = $containerProphecy->reveal();

		$factory = new ServiceFactory();
		$service = $factory($container, Service::class);
		$this->tester->assertInstanceOf(Service::class, $service);
	}
}