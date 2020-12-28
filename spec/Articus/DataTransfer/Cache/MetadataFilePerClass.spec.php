<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Cache;

use spec\Example;
use spec\Utility\GlobalFunctionMock;
use Articus\DataTransfer as DT;

\describe(DT\Cache\MetadataFilePerClass::class, function ()
{
	\context('null as cache folder', function ()
	{
		\it('always gets default value', function ()
		{
			$cache = new DT\Cache\MetadataFilePerClass(null);
			$defaultValue = \mock();
			\expect($cache->get('test', $defaultValue))->toBe($defaultValue);
		});
		\it('never sets value to cache', function ()
		{
			$cache = new DT\Cache\MetadataFilePerClass(null);
			\expect($cache->set('test', \mock()))->toBe(false);
		});
	});
	\context('string as cache folder', function ()
	{
		\beforeAll(function ()
		{
			$this->cacheKey = Example\DTO\Data::class;
			$this->cacheFolder = 'data/cache';
			$this->cacheFile = $this->cacheFolder . '/' . \str_replace('\\', '/', $this->cacheKey) . '.metadata.php';
			$this->cacheData = ['test' => 123];
			$this->cacheContent = <<<'CACHE_CONTENT'
<?php return array (
  'test' => 123,
);
CACHE_CONTENT;
		});
		\beforeEach(function ()
		{
			if (\is_dir($this->cacheFolder))
			{
				$files = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($this->cacheFolder, \FilesystemIterator::SKIP_DOTS),
					\RecursiveIteratorIterator::CHILD_FIRST
				);
				foreach ($files as $name => $file)
				{
					if ($file->isDir())
					{
						\rmdir($name);
					}
					else
					{
						\unlink($name);
					}
				}
				\rmdir($this->cacheFolder);
			}
		});
		\afterEach(function ()
		{
			GlobalFunctionMock::reset();
		});
		\it('creates cache folder', function ()
		{
			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			\expect(\is_dir($this->cacheFolder))->toBe(true);
		});
		\it('throws if cache folder does not exists and can not be created', function ()
		{
			\skipIf(GlobalFunctionMock::disabled());
			GlobalFunctionMock::stub('is_dir')->with($this->cacheFolder)->andReturn(false);
			GlobalFunctionMock::stub('mkdir')->with($this->cacheFolder, 0775, true)->andReturn(false);
			$exception = new \InvalidArgumentException(\sprintf('The directory "%s" does not exist and could not be created.', $this->cacheFolder));
			\expect(function ()
			{
				$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			})->toThrow($exception);
		});
		\it('throws if cache folder exists but is not writable', function ()
		{
			\skipIf(GlobalFunctionMock::disabled());
			GlobalFunctionMock::stub('is_dir')->with($this->cacheFolder)->andReturn(true);
			GlobalFunctionMock::stub('mkdir')->with($this->cacheFolder, 0775, true)->andReturn(true);
			GlobalFunctionMock::stub('is_writable')->with($this->cacheFolder)->andReturn(false);
			$exception = new \InvalidArgumentException(\sprintf('The directory "%s" is not writable.', $this->cacheFolder));
			\expect(function ()
			{
				$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			})->toThrow($exception);
		});
		\it('gets default value if cache is empty', function ()
		{
			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			$defaultValue = \mock();
			\expect($cache->get($this->cacheKey, $defaultValue))->toBe($defaultValue);
		});
		\it('gets cached value stored in file', function ()
		{
			$folder = \dirname($this->cacheFile);
			\expect(\mkdir($folder, 0777, true))->toBe(true);
			\expect(\file_put_contents($this->cacheFile, $this->cacheContent))->not->toBe(false);

			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			\expect($cache->get($this->cacheKey))->toBe($this->cacheData);
		});
		\it('does not set value to cache if value is not array', function ()
		{
			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			\expect($cache->set($this->cacheKey, new \stdClass()))->toBe(false);
		});
		\it('does not set value to cache if value has TTL', function ()
		{
			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			\expect($cache->set($this->cacheKey, [], 1))->toBe(false);
		});
		\it('does not set value to cache if it can not save value to temporary file', function ()
		{
			\skipIf(GlobalFunctionMock::disabled());
			$temporaryFilename = 'test';
			$folder = \dirname($this->cacheFile);
			GlobalFunctionMock::stub('tempnam')->withArgs(
				function (string $directory, string $prefix) use ($folder)
				{
					return (($directory === \realpath($folder)) && ($prefix === 'swap'));
				}
			)->andReturn($temporaryFilename);
			GlobalFunctionMock::stub('file_put_contents')->with($temporaryFilename, $this->cacheContent)->andReturn(false);
			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			\expect($cache->set($this->cacheKey, $this->cacheData))->toBe(false);
		});
		\it('does not set value to cache if it can not move temporary file to permanent location', function ()
		{
			\skipIf(GlobalFunctionMock::disabled());
			$temporaryFilename = 'test';
			$folder = \dirname($this->cacheFile);
			GlobalFunctionMock::stub('tempnam')->withArgs(
				function (string $directory, string $prefix) use ($folder)
				{
					return (($directory === \realpath($folder)) && ($prefix === 'swap'));
				}
			)->andReturn($temporaryFilename);
			GlobalFunctionMock::stub('file_put_contents')->with($temporaryFilename, $this->cacheContent)->andReturn(true);
			GlobalFunctionMock::stub('chmod')->with($temporaryFilename, 0664)->andReturn(true);
			GlobalFunctionMock::stub('rename')->withArgs(
				function (string $from, string $to) use ($temporaryFilename, $folder)
				{
					return (($from === $temporaryFilename) && ($to === \str_replace($folder, \realpath($folder), $this->cacheFile)));
				}
			)->andReturn(false);
			GlobalFunctionMock::stub('unlink')->with($temporaryFilename)->andReturn(true);
			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			\expect($cache->set($this->cacheKey, $this->cacheData))->toBe(false);
		});
		\it('sets value to cache by storing in file', function ()
		{
			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			\expect($cache->set($this->cacheKey, $this->cacheData))->toBe(true);
			\expect(\file_exists($this->cacheFile))->toBe(true);
			\expect(\file_get_contents($this->cacheFile))->toBe($this->cacheContent);
		});
		\it('throws on PSR-16 methods that are not needed for metadata provider', function ()
		{
			$exception = new \LogicException('Not implemented');
			$cache = new DT\Cache\MetadataFilePerClass($this->cacheFolder);
			\expect(function () use ($cache)
			{
				$cache->has($this->cacheKey);
			})->toThrow($exception);
			\expect(function () use ($cache)
			{
				$cache->delete($this->cacheKey);
			})->toThrow($exception);
			\expect(function () use ($cache)
			{
				$cache->clear();
			})->toThrow($exception);
			\expect(function () use ($cache)
			{
				$cache->getMultiple([$this->cacheKey]);
			})->toThrow($exception);
			\expect(function () use ($cache)
			{
				$cache->setMultiple([$this->cacheKey => $this->cacheData]);
			})->toThrow($exception);
			\expect(function () use ($cache)
			{
				$cache->deleteMultiple([$this->cacheKey]);
			})->toThrow($exception);
		});
	});
});
