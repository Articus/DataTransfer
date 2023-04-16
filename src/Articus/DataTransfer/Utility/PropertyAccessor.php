<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Utility;

/**
 * Provides single interface to access object properties using metadata declarations.
 */
class PropertyAccessor
{
	protected object $object;

	/**
	 * @param object $object object which properties you want to access
	 */
	public function __construct(object &$object)
	{
		$this->object = &$object;
	}

	/**
	 * @param null|array $getter tuple (<name of property or method>, <flag if getter is method>)
	 * @param mixed $default default value to return if there is no getter
	 * @return mixed
	 */
	public function get(?array $getter, $default = null)
	{
		$result = $default;
		if ($getter !== null)
		{
			[$name, $isMethod] = $getter;
			if ($isMethod)
			{
				$result = $this->object->{$name}();
			}
			else
			{
				$result = $this->object->{$name};
			}
		}
		return $result;
	}

	/**
	 * @param null|array $setter tuple (<name of property or method>, <flag if setter is method>)
	 * @param mixed $value
	 */
	public function set(?array $setter, $value): void
	{
		if ($setter !== null)
		{
			[$name, $isMethod] = $setter;
			if ($isMethod)
			{
				$this->object->{$name}($value);
			}
			else
			{
				$this->object->{$name} = $value;
			}
		}
	}
}
