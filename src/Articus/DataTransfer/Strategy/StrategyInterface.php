<?php

namespace Articus\DataTransfer\Strategy;
/**
 * Value hydration strategy, similar to Zend\Hydrator\Strategy\StrategyInterface, but with one major difference:
 * "hydrate" receives value currently stored in object
 */
interface StrategyInterface
{
	/**
	 * Converts the given value so that it can be extracted by the hydrator.
	 *
	 * @param mixed   $objectValue The original value.
	 * @param object $object (optional) The original object for context.
	 * @return mixed Returns the value that should be extracted.
	 */
	public function extract($objectValue, $object = null);

	/**
	 * Converts the given value so that it can be hydrated by the hydrator.
	 *
	 * @param mixed $arrayValue The original value.
	 * @param mixed $objectValue The current value stored in object.
	 * @param array $array (optional) The original data for context.
	 * @return mixed Returns the value that should be hydrated.
	 */
	public function hydrate($arrayValue, $objectValue, array $array = null);

}