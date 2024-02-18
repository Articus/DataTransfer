<?php
declare(strict_types=1);

namespace Articus\DataTransfer\MetadataCache;

use Closure;
use InvalidArgumentException;
use function array_push;
use function chmod;
use function explode;
use function file_put_contents;
use function implode;
use function is_array;
use function is_dir;
use function is_writable;
use function mkdir;
use function pathinfo;
use function realpath;
use function rename;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function tempnam;
use function unlink;
use function var_export;
use const DIRECTORY_SEPARATOR;
use const PATHINFO_DIRNAME;

/**
 * Metadata for class \My\CustomClass is stored as plain PHP array in file <directory>/My/CustomClass.metadata.php
 */
class FilePerClass implements MetadataCacheInterface
{
	/**
	 * Root folder to store metadata
	 */
	protected ?string $directory = null;
	/**
	 * Permissions that should be removed from files and folders used to store metadata.
	 * Similar to https://www.php.net/manual/en/function.umask.php
	 */
	protected int $umask;

	protected static Closure $emptyErrorHandler;

	public function __construct(?string $directory, int $umask = 0002)
	{
		$this->umask = $umask;// It has to be before createDirectoryIfNeeded()
		if ($directory !== null)
		{
			if (!$this->createDirectoryIfNeeded($directory))
			{
				throw new InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $directory));
			}
			if (!is_writable($directory))
			{
				throw new InvalidArgumentException(sprintf('The directory "%s" is not writable.', $directory));
			}
			$this->directory = realpath($directory);// It has to be after createDirectoryIfNeeded()
		}
		self::$emptyErrorHandler = static function() {};
	}

	/**
	 * @inheritDoc
	 */
	public function get(string $className): ?array
	{
		$result = null;
		if ($this->directory !== null)
		{
			$filename = $this->getFilename($this->directory, $className);
			// note: error suppression is still faster than `file_exists`, `is_file` and `is_readable`
			set_error_handler(self::$emptyErrorHandler);
			$metadata = include $filename;
			restore_error_handler();
			if (is_array($metadata))
			{
				$result = $metadata;
			}
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function set(string $className, array $metadata): bool
	{
		$result = false;
		if ($this->directory !== null)
		{
			$filename = $this->getFilename($this->directory, $className);
			$content  = sprintf('<?php return %s;', var_export($metadata, true));
			$result = $this->writeFile($filename, $content);
		}
		return $result;
	}

	/**
	 * @param string $directory
	 * @param class-string $className
	 * @return string
	 */
	protected function getFilename(string $directory, string $className): string
	{
		$pathParts = [$directory];
		array_push($pathParts, ...explode('\\', $className));
		return implode(DIRECTORY_SEPARATOR, $pathParts) . '.metadata.php';
	}

	protected function writeFile(string $filename, string $content): bool
	{
		$result = false;
		$directory = pathinfo($filename, PATHINFO_DIRNAME);

		if ($this->createDirectoryIfNeeded($directory) && is_writable($directory))
		{
			$temporaryFilename = tempnam($directory, 'swap');
			if (file_put_contents($temporaryFilename, $content) !== false)
			{
				@chmod($temporaryFilename, 0666 & (~$this->umask));
				if (@rename($temporaryFilename, $filename))
				{
					$result = true;
				}
				else
				{
					@unlink($temporaryFilename);
				}
			}
		}
		return $result;
	}

	protected function createDirectoryIfNeeded(string $directory) : bool
	{
		return (is_dir($directory) || @mkdir($directory, 0777 & (~$this->umask), true));
	}
}
