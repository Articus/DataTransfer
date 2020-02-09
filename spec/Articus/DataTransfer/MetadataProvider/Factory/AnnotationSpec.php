<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\MetadataProvider\Factory;

use Articus\DataTransfer as DT;
use Doctrine\Common\Cache\VoidCache;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Doctrine\Common\Cache\Cache as CacheStorage;

/**
 * TODO add expected text for LogicExceptions
 */
class AnnotationSpec extends ObjectBehavior
{
	public function it_gets_configuration_from_default_config_key(ContainerInterface $container, \ArrayAccess $config)
	{
		$configKey = DT\MetadataProvider\Annotation::class;
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce();

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
	}

	public function it_gets_configuration_from_custom_config_key(ContainerInterface $container, \ArrayAccess $config)
	{
		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce();

		$this->beConstructedWith($configKey);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
	}

	public function it_constructs_itself_and_gets_configuration_from_custom_config_key(ContainerInterface $container, \ArrayAccess $config)
	{
		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce();

		$service = $this::__callStatic($configKey, [$container, '', null]);
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
	}

	public function it_throws_on_too_few_arguments_during_self_construct(ContainerInterface $container)
	{
		$configKey = 'test_config_key';
		$error = new \InvalidArgumentException(\sprintf(
			'To invoke %s with custom configuration key statically 3 arguments are required: container, service name and options.',
			DT\MetadataProvider\Factory\Annotation::class
		));

		$this::shouldThrow($error)->during('__callStatic', [$configKey, []]);
		$this::shouldThrow($error)->during('__callStatic', [$configKey, [$container]]);
		$this::shouldThrow($error)->during('__callStatic', [$configKey, [$container, '']]);
	}

	public function it_creates_service_with_empty_configuration(ContainerInterface $container)
	{
		$container->get('config')->shouldBeCalledOnce()->willReturn([]);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
		$service->shouldHavePropertyOfType('cacheStorage', VoidCache::class);
	}

	public function it_creates_service_with_cache_storage_configuration(ContainerInterface $container)
	{
		$config = [
			DT\MetadataProvider\Annotation::class => ['cache' => ['directory' => 'data/cache']]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
		$service->shouldHavePropertyOfType('cacheStorage', DT\Cache\Annotation::class);
	}

	public function it_creates_service_with_cache_storage_from_container(ContainerInterface $container, CacheStorage $cacheStorage)
	{
		$cacheServiceName = 'test_cache_service';
		$config = [
			DT\MetadataProvider\Annotation::class => ['cache' => $cacheServiceName]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($cacheServiceName)->shouldBeCalledOnce()->willReturn(true);
		$container->get($cacheServiceName)->shouldBeCalledOnce()->willReturn($cacheStorage);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
		$service->shouldHaveProperty('cacheStorage', $cacheStorage);
	}

	public function it_throws_on_invalid_cache_storage_in_container(ContainerInterface $container, $cacheStorage)
	{
		$cacheServiceName = 'test_cache_service';
		$config = [
			DT\MetadataProvider\Annotation::class => ['cache' => $cacheServiceName]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($cacheServiceName)->shouldBeCalledOnce()->willReturn(true);
		$container->get($cacheServiceName)->shouldBeCalledOnce()->willReturn($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, '']);
	}

	public function it_throws_on_invalid_cache_storage_configuration(ContainerInterface $container)
	{
		$config = [
			DT\MetadataProvider\Annotation::class => ['cache' => new \stdClass()]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, '']);
	}
}
