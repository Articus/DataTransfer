<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Cache;

use Doctrine\Common\Cache\FileCache;

/**
 * Simple custom cache driver for Articus\DataTransfer\MetadataProvider\Annotation
 * that is optimized to store scalar multidimensional arrays infinitely.
 * @see \Doctrine\Common\Cache\PhpFileCache
 * @see \Articus\DataTransfer\MetadataProvider\Factory\Annotation
 */
class Annotation extends FileCache
{
	/**
	 * @var \Closure
	 */
	protected static $emptyErrorHandler;

	public function __construct($directory, $extension = '.php', $umask = 0002)
	{
		parent::__construct($directory, $extension, $umask);
		self::$emptyErrorHandler = static function() {};
	}

	/**
	 * @inheritDoc
	 */
	protected function doFetch($id)
	{
		return ($this->includeFileForId($id) ?? false);
	}

	/**
	 * @inheritDoc
	 */
	protected function doContains($id)
	{
		return ($this->includeFileForId($id) !== null);
	}

	/**
	 * @inheritDoc
	 */
	protected function doSave($id, $data, $lifeTime = 0)
	{
		$result = false;
		if (\is_array($data) && ($lifeTime === 0))
		{
			$filename = $this->getFilename($id);
			$code  = \sprintf('<?php return %s;', \var_export($data, true));
			$result = $this->writeFile($filename, $code);
		}
		return $result;
	}

	/**
	 * @return array|null
	 */
	protected function includeFileForId(string $id) : ?array
	{
		$fileName = $this->getFilename($id);

		// note: error suppression is still faster than `file_exists`, `is_file` and `is_readable`
		\set_error_handler(self::$emptyErrorHandler);
		$value = include $fileName;
		\restore_error_handler();

		return (\is_array($value) ? $value : null);
	}
}
