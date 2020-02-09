<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Cache;

use spec\Example;
use Articus\DataTransfer as DT;

const CACHE_KEY = Example\DTO\Data::class;
const CACHE_FOLDER = 'data/cache';
const CACHE_FILE = CACHE_FOLDER . '/d2/5b737065635c4578616d706c655c44544f5c446174615d5b315d.php';
const CACHE_DATA = ['test' => 123];
const CACHE_TEXT = <<<'CACHE_TEXT'
<?php return array (
  'test' => 123,
);
CACHE_TEXT;

\describe(DT\Cache\Annotation::class, function ()
{
	\beforeEach(function ()
	{
		if (\file_exists(CACHE_FILE))
		{
			\unlink(CACHE_FILE);
		}
	});
	\it('does not find and does not fetch cached value if cache is empty', function ()
	{
		$cache = new DT\Cache\Annotation(CACHE_FOLDER);
		\expect($cache->fetch(CACHE_KEY))->toBe(false);
		\expect($cache->contains(CACHE_KEY))->toBe(false);
	});
	\it('finds and fetches cached value stored in file', function ()
	{
		$folder = \dirname(CACHE_FILE);
		if (!\file_exists($folder))
		{
			\expect(\mkdir($folder, 0777, true))->toBe(true);
		}
		\expect(\file_put_contents(CACHE_FILE, CACHE_TEXT))->not->toBe(false);
		$cache = new DT\Cache\Annotation(CACHE_FOLDER);
		\expect($cache->fetch(CACHE_KEY))->toBe(CACHE_DATA);
		\expect($cache->contains(CACHE_KEY))->toBe(true);
	});
	\it('does not save value to cache if value is not array', function ()
	{
		$cache = new DT\Cache\Annotation(CACHE_FOLDER);
		\expect($cache->save(CACHE_KEY, new \stdClass()))->toBe(false);
	});
	\it('does not save value to cache if value has TTL', function ()
	{
		$cache = new DT\Cache\Annotation(CACHE_FOLDER);
		\expect($cache->save(CACHE_KEY, [], 1))->toBe(false);
	});
	\it('saves value to cache in file', function ()
	{
		$cache = new DT\Cache\Annotation(CACHE_FOLDER);
		\expect($cache->save(CACHE_KEY, CACHE_DATA))->toBe(true);
		\expect(\file_exists(CACHE_FILE))->toBe(true);
		\expect(\file_get_contents(CACHE_FILE))->toBe(CACHE_TEXT);
	});
});
