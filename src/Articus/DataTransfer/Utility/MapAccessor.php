<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Utility;

/**
 * Provides single interface to access different variations of key-value maps: arrays, stdClasses, implementations of ArrayAccess.
 */
class MapAccessor
{
	/**
	 * @var array|\stdClass|\ArrayAccess
	 */
	protected $data;

	/**
	 * @var bool
	 */
	protected $isArray;

	/**
	 * @var bool
	 */
	protected $isStdClass;

	/**
	 * @param array|\stdClass|\ArrayAccess $data possible key-value map you want to access
	 */
	public function __construct(&$data)
	{
		$this->data = &$data;
		$this->isArray = (\is_array($data) || ($data instanceof \ArrayAccess));
		$this->isStdClass = ($data instanceof \stdClass);
	}

	/**
	 * @return bool
	 */
	public function accessible(): bool
	{
		return ($this->isArray || $this->isStdClass);
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		$result = $default;
		if ($this->isArray)
		{
			$result = $this->data[$key] ?? $default;
		}
		elseif ($this->isStdClass)
		{
			$result = $this->data->{$key} ?? $default;
		}
		return $result;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set(string $key, $value): void
	{
		if ($this->isArray)
		{
			$this->data[$key] = $value;
		}
		elseif ($this->isStdClass)
		{
			$this->data->{$key} = $value;
		}
	}
}
