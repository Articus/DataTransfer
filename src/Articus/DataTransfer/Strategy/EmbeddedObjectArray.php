<?php

namespace Articus\DataTransfer\Strategy;

class EmbeddedObjectArray extends EmbeddedObject
{
	/**
	 * @inheritDoc
	 */
	public function extract($objectValue, $object = null)
	{
		$result = null;
		if (is_array($objectValue) || ($objectValue instanceof \Traversable))
		{
			$result = [];
			foreach ($objectValue as $item)
			{
				$result[] = parent::extract($item, $object);
			}
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($arrayValue, $objectValue, array $array = null)
	{
		$result = null;
		if (is_array($arrayValue))
		{
			$result = [];
			foreach ($arrayValue as $item)
			{
				$result[] = parent::hydrate($item, null, $array);
			}
		}
		return $result;
	}
}