<?php
declare(strict_types=1);

namespace spec\Matcher;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Matcher\Matcher;
use PhpSpec\Wrapper\DelayedCall;
use ReflectionClass;
use function count;
use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Allows to check what type has value of non-public property
 * TODO find a better way to check state of the object created by the factory
 */
class PropertyValueType implements Matcher
{
	/**
	 * @param string $name
	 * @param object $subject
	 * @param array $arguments
	 * @return bool
	 */
	public function supports(string $name, $subject, array $arguments): bool
	{
		return ($name === 'havePropertyOfType') && is_object($subject)
			&& (2 == count($arguments)) && is_string($arguments[0]) && is_string($arguments[1])
		;
	}

	public function getPriority(): int
	{
		return 100;
	}

	public function positiveMatch(string $name, $subject, array $arguments): ?DelayedCall
	{
		[$propertyName, $expectedPropertyValueType, $actualPropertyValueType] = $this->processProperty($subject, $arguments);
		if ($actualPropertyValueType !== $expectedPropertyValueType)
		{
			throw new FailureException(sprintf(
				'Expected "%s" property value of %s instance to have type %s, but got %s.',
				$propertyName,
				get_class($subject),
				$expectedPropertyValueType,
				$actualPropertyValueType
			));
		}
		return null;
	}

	public function negativeMatch(string $name, $subject, array $arguments): ?DelayedCall
	{
		[$propertyName, $expectedPropertyValueType, $actualPropertyValueType] = $this->processProperty($subject, $arguments);
		if ($actualPropertyValueType === $expectedPropertyValueType)
		{
			throw new FailureException(sprintf(
				'Did not expect "%s" property value of %s instance to be %s, but got one.',
				$propertyName,
				get_class($subject),
				$expectedPropertyValueType
			));
		}
		return null;
	}

	protected function processProperty(object $subject, array $arguments): array
	{
		[$propertyName, $expectedPropertyValueType] = $arguments;
		$classReflection = new ReflectionClass($subject);
		$propertyReflection = $classReflection->getProperty($propertyName);
		$propertyReflection->setAccessible(true);
		$actualPropertyValue = $propertyReflection->getValue($subject);
		$actualPropertyValueType = is_object($actualPropertyValue) ? get_class($actualPropertyValue) : gettype($actualPropertyValue);
		return [$propertyName, $expectedPropertyValueType, $actualPropertyValueType];
	}
}
