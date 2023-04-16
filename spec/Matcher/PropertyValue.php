<?php
declare(strict_types=1);

namespace spec\Matcher;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Matcher\Matcher;
use PhpSpec\Wrapper\DelayedCall;
use ReflectionClass;
use function count;
use function get_class;
use function is_object;
use function is_string;
use function sprintf;
use function var_export;

/**
 * Allows to check value of non-public properties
 * TODO find a better way to check state of the object created by the factory
 */
class PropertyValue implements Matcher
{
	/**
	 * @param string $name
	 * @param object $subject
	 * @param array $arguments
	 * @return bool
	 */
	public function supports(string $name, $subject, array $arguments): bool
	{
		return ($name === 'haveProperty') && is_object($subject) && (2 == count($arguments)) && is_string($arguments[0]);
	}

	public function getPriority(): int
	{
		return 100;
	}

	public function positiveMatch(string $name, $subject, array $arguments): ?DelayedCall
	{
		[$propertyName, $expectedPropertyValue, $actualPropertyValue] = $this->processProperty($subject, $arguments);
		if ($actualPropertyValue !== $expectedPropertyValue)
		{
			throw new FailureException(sprintf(
				'Expected "%s" property value of %s instance to be %s, but got %s.',
				$propertyName,
				get_class($subject),
				var_export($expectedPropertyValue, true),
				var_export($actualPropertyValue, true)
			));
		}
		return null;
	}

	public function negativeMatch(string $name, $subject, array $arguments): ?DelayedCall
	{
		[$propertyName, $expectedPropertyValue, $actualPropertyValue] = $this->processProperty($subject, $arguments);
		if ($actualPropertyValue === $expectedPropertyValue)
		{
			throw new FailureException(sprintf(
				'Did not expect "%s" property value of %s instance to be %s, but got one.',
				$propertyName,
				get_class($subject),
				var_export($expectedPropertyValue, true)
			));
		}
		return null;
	}

	protected function processProperty(object $subject, array $arguments): array
	{
		[$propertyName, $expectedPropertyValue] = $arguments;
		$classReflection = new ReflectionClass($subject);
		$propertyReflection = $classReflection->getProperty($propertyName);
		$propertyReflection->setAccessible(true);
		$actualPropertyValue = $propertyReflection->getValue($subject);
		return [$propertyName, $expectedPropertyValue, $actualPropertyValue];
	}
}
