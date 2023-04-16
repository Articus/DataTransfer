<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\MetadataProvider\Factory;

use ArrayAccess;
use Articus\DataTransfer as DT;
use LogicException;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use stdClass;
use const PHP_MAJOR_VERSION;

/**
 * TODO add expected text for LogicExceptions
 */
class PhpAttributeSpec extends ObjectBehavior
{
	public function let()
	{
		if (PHP_MAJOR_VERSION < 8)
		{
			throw new SkippingException('PHP 8+ is required');
		}
	}

	public function it_gets_configuration_from_default_config_key(ContainerInterface $container, ArrayAccess $config)
	{
		$configKey = DT\MetadataProvider\PhpAttribute::class;
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\PhpAttribute::class);
	}

	public function it_gets_configuration_from_custom_config_key(ContainerInterface $container, ArrayAccess $config)
	{
		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($configKey);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\PhpAttribute::class);
	}

	public function it_constructs_itself_and_gets_configuration_from_custom_config_key(ContainerInterface $container, ArrayAccess $config)
	{
		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$service = $this::__callStatic($configKey, [$container, '', null]);
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\PhpAttribute::class);
	}

	public function it_creates_service_with_empty_configuration(ContainerInterface $container)
	{
		$container->get('config')->shouldBeCalledOnce()->willReturn([]);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\PhpAttribute::class);
		$service->shouldHavePropertyOfType('cache', DT\Cache\MetadataFilePerClass::class);
	}

	public function it_creates_service_with_cache_configuration(ContainerInterface $container)
	{
		$config = [
			DT\MetadataProvider\Annotation::class => ['cache' => ['directory' => 'data/cache']]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\PhpAttribute::class);
		$service->shouldHavePropertyOfType('cache', DT\Cache\MetadataFilePerClass::class);
	}

	public function it_creates_service_with_cache_from_container(ContainerInterface $container, CacheInterface $cache)
	{
		$cacheServiceName = 'test_cache_service';
		$config = [
			DT\MetadataProvider\PhpAttribute::class => ['cache' => $cacheServiceName]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($cacheServiceName)->shouldBeCalledOnce()->willReturn(true);
		$container->get($cacheServiceName)->shouldBeCalledOnce()->willReturn($cache);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\PhpAttribute::class);
		$service->shouldHaveProperty('cache', $cache);
	}

	public function it_throws_on_invalid_cache_service_in_container(ContainerInterface $container, $cache)
	{
		$cacheServiceName = 'test_cache_service';
		$config = [
			DT\MetadataProvider\PhpAttribute::class => ['cache' => $cacheServiceName]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($cacheServiceName)->shouldBeCalledOnce()->willReturn(true);
		$container->get($cacheServiceName)->shouldBeCalledOnce()->willReturn($cache);
		$this->shouldThrow(LogicException::class)->during('__invoke', [$container, '']);
	}

	public function it_throws_on_invalid_cache_configuration(ContainerInterface $container)
	{
		$config = [
			DT\MetadataProvider\PhpAttribute::class => ['cache' => new stdClass()]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(LogicException::class)->during('__invoke', [$container, '']);
	}
}
