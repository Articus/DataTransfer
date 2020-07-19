<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Incomplete implementation of PSR-16 optimized to store metadata.
 * Metadata for class \My\CustomClass is stored as plain PHP array in file <directory>/My/CustomClass.metadata.php
 */
class MetadataFilePerClass implements CacheInterface
{
	/**
	 * Root folder to store metadata
	 * @var string|null
	 */
	protected $directory;
	/**
	 * Permissions that should be removed from files and folders used to store metadata.
	 * Similar to https://www.php.net/manual/en/function.umask.php
	 * @var int
	 */
	protected $umask;
	/**
	 * @var \Closure
	 */
	protected static $emptyErrorHandler;

	/**
	 * @param string|null $directory
	 * @param int $umask
	 */
	public function __construct(?string $directory, int $umask = 0002)
	{
		$this->umask = $umask;// It has to be before createDirectoryIfNeeded()
		if ($directory !== null)
		{
			if (!$this->createDirectoryIfNeeded($directory))
			{
				throw new \InvalidArgumentException(\sprintf('The directory "%s" does not exist and could not be created.', $directory));
			}
			if (!\is_writable($directory))
			{
				throw new \InvalidArgumentException(\sprintf('The directory "%s" is not writable.', $directory));
			}
			$this->directory = \realpath($directory);// It has to be after createDirectoryIfNeeded()
		}
		self::$emptyErrorHandler = static function() {};
	}

	/**
	 * @inheritDoc
	 */
	public function get($key, $default = null)
	{
		$result = $default;
		if ($this->directory !== null)
		{
			$filename = $this->getFilename($this->directory, $key);
			// note: error suppression is still faster than `file_exists`, `is_file` and `is_readable`
			\set_error_handler(self::$emptyErrorHandler);
			$value = include $filename;
			\restore_error_handler();
			if (\is_array($value))
			{
				$result = $value;
			}
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function set($key, $value, $ttl = null)
	{
		$result = false;
		if (($this->directory !== null) && \is_array($value) && ($ttl === null))
		{
			$filename = $this->getFilename($this->directory, $key);
			$content  = \sprintf('<?php return %s;', \var_export($value, true));
			$result = $this->writeFile($filename, $content);
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function delete($key)
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function clear()
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function getMultiple($keys, $default = null)
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function setMultiple($values, $ttl = null)
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function deleteMultiple($keys)
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function has($key)
	{
		throw new \LogicException('Not implemented');
	}

	protected function getFilename(string $directory, string $key): string
	{
		$pathParts = [$directory];
		\array_push($pathParts, ...\explode('\\', $key));
		return \implode(\DIRECTORY_SEPARATOR, $pathParts) . '.metadata.php';
	}

	protected function writeFile(string $filename, string $content): bool
	{
		$result = false;
		$directory = \pathinfo($filename, \PATHINFO_DIRNAME);

		if ($this->createDirectoryIfNeeded($directory) && \is_writable($directory))
		{
			$temporaryFilename = \tempnam($directory, 'swap');
			if (\file_put_contents($temporaryFilename, $content) !== false)
			{
				@\chmod($temporaryFilename, 0666 & (~$this->umask));
				if (@\rename($temporaryFilename, $filename))
				{
					$result = true;
				}
				else
				{
					@\unlink($temporaryFilename);
				}
			}
		}
		return $result;
	}

	protected function createDirectoryIfNeeded(string $directory) : bool
	{
		return (\is_dir($directory) || @\mkdir($directory, 0777 & (~$this->umask), true));
	}
}
