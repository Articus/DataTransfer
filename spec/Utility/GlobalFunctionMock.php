<?php
declare(strict_types=1);

namespace spec\Utility;

use Mockery\Expectation;
use Mockery\ExpectationInterface;
use Mockery\HigherOrderMessage;
use Mockery\MockInterface;
use function array_keys;
use function extension_loaded;
use function mock;
use function uopz_set_return;
use function uopz_unset_return;

class GlobalFunctionMock
{
	protected static ?MockInterface $innerMock = null;
	/**
	 * @var string[]
	 */
	protected static array $functionNameMap = [];

	public static function disabled(): bool
	{
		return (!extension_loaded('uopz'));
	}

	/**
	 * @param string $functionName
	 * @return Expectation|ExpectationInterface|HigherOrderMessage
	 */
	public static function stub(string $functionName)
	{
		if (self::$innerMock === null)
		{
			self::$innerMock = mock();
		}
		if (!isset(self::$functionNameMap[$functionName]))
		{
			$mock = self::$innerMock;
			uopz_set_return(
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
		foreach (array_keys(self::$functionNameMap) as $functionName)
		{
			uopz_unset_return($functionName);
			unset(self::$functionNameMap[$functionName]);
		}
		self::$innerMock = null;
	}
}
