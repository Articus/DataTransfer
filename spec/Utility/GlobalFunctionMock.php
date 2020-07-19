<?php
declare(strict_types=1);

namespace spec\Utility;

use Mockery\Expectation;
use Mockery\MockInterface;

class GlobalFunctionMock
{
	/**
	 * @var MockInterface
	 */
	protected static $innerMock;
	/**
	 * @var string[]
	 */
	protected static $functionNameMap = [];

	/**
	 * @param string $functionName
	 * @return Expectation|\Mockery\ExpectationInterface|\Mockery\HigherOrderMessage
	 */
	public static function stub(string $functionName)
	{
		if (!isset(self::$innerMock))
		{
			self::$innerMock = \mock();
		}
		if (!isset(self::$functionNameMap[$functionName]))
		{
			$mock = self::$innerMock;
			\uopz_set_return(
				$functionName,
				function(...$arguments) use ($mock, $functionName)
				{
					return $mock->{$functionName}(...$arguments);
				},
				true
			);
			self::$functionNameMap[$functionName] = true;
		}
		return self::$innerMock->shouldReceive($functionName);
	}

	public static function reset(): void
	{
		foreach (\array_keys(self::$functionNameMap) as $functionName)
		{
			\uopz_unset_return($functionName);
			unset(self::$functionNameMap[$functionName]);
		}
		self::$innerMock = null;
	}
}