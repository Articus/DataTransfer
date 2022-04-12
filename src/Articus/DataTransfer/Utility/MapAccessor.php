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
	 * @var bool
	 */
	protected $isArrayAccess;

	/**
	 * @param array|\stdClass|\ArrayAccess $data possible key-value map you want to access
	 */
	public function __construct(&$data)
	{
		$this->data = &$data;
		$this->isArray = \is_array($data);
		$this->isStdClass = ($data instanceof \stdClass);
		$this->isArrayAccess = ($data instanceof \ArrayAccess);
	}

	/**
	 * @return bool
	 */
	public function accessible(): bool
	{
		return ($this->isArray || $this->isStdClass || $this->isArrayAccess);
	}

	/**
	 * @param string|int $key
	 * @return bool
	 */
	public function has($key): bool
	{
		return (
			($this->isArray && \array_key_exists($key, $this->data))
			|| ($this->isStdClass && \property_exists($this->data, $key))
			|| ($this->isArrayAccess && $this->data->offsetExists($key))
		);
	}

	/**
	 * @param string|int $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = null)
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
		elseif ($this->isArrayAccess)
		{
			$result = $this->data->offsetGet($key) ?? $default;
		}
		return $result;
	}

	/**
	 * @param string|int $key
	 * @param mixed $value
	 */
	public function set($key, $value): void
	{
		if ($this->isArray)
		{
			$this->data[$key] = $value;
		}
		elseif ($this->isStdClass)
		{
			$this->data->{$key} = $value;
		}
		elseif ($this->isArrayAccess)
		{
			$this->data->offsetSet($key, $value);
		}
	}

	/**
	 * @param string|int $key
	 */
	public function remove($key): void
	{
		if ($this->isArray)
		{
			unset($this->data[$key]);
		}
		elseif ($this->isStdClass)
		{
			unset($this->data->{$key});
		}
		elseif ($this->isArrayAccess)
		{
			$this->data->offsetUnset($key);
		}
	}
}
