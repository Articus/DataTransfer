<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\MetadataProvider\Factory;

use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

/**
 * TODO add expected text for LogicExceptions
 */
class AnnotationSpec extends ObjectBehavior
{
	public function it_creates_service_with_default_configuration(ContainerInterface $container)
	{
		$container->get('config')->shouldBeCalledOnce()->willReturn([]);
		$this->__invoke($container, '')->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
	}

	public function it_creates_service_with_cache_storage_configuration(ContainerInterface $container)
	{
		$config = [
			DT\MetadataProvider\Annotation::class => ['cache' => ['adapter' => 'memory']]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->__invoke($container, '')->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
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
		$this->__invoke($container, '')->shouldBeAnInstanceOf(DT\MetadataProvider\Annotation::class);
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

	public function it_throws_on_empty_cache_storage_configuration(ContainerInterface $container)
	{
		$config = [
			DT\MetadataProvider\Annotation::class => ['cache' => null]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
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
