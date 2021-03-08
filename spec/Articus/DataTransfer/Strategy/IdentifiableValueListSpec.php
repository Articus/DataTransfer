<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class IdentifiableValueListSpec extends ObjectBehavior
{
	public function it_throws_on_extract_from_non_iterable(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null);
		$this->shouldThrow(\InvalidArgumentException::class)->during('extract', [null]);
		$this->shouldThrow(\InvalidArgumentException::class)->during('extract', [1]);
		$this->shouldThrow(\InvalidArgumentException::class)->during('extract', ['test']);
		$this->shouldThrow(\InvalidArgumentException::class)->during('extract', [new \stdClass()]);
	}

	public function it_extracts_from_empty_iterable(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier,
		\Iterator $iterator
	)
	{
		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null);
		$this->extract([])->shouldBe([]);
		$this->extract($iterator)->shouldBe([]);
	}

	public function it_extracts_from_non_empty_iterable_using_value_strategy(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$source = [
			0 => 'test1',
			1 => 'test2',
			10 => 'test3',
		];
		$destination = [
			123,
			456,
			789,
		];
		$valueStrategy->extract($source[0])->shouldBeCalledOnce()->willReturn($destination[0]);
		$valueStrategy->extract($source[1])->shouldBeCalledOnce()->willReturn($destination[1]);
		$valueStrategy->extract($source[10])->shouldBeCalledOnce()->willReturn($destination[2]);

		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null);
		$this->extract($source)->shouldBe($destination);
	}

	public function it_wraps_and_rethrows_invalid_data_exceptions_from_value_strategy(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$source = [
			0 => 'test1',
			1 => 'test2',
			10 => 'test3',
			11 => 'test4',
		];
		$violations = [
			'test' => 12345,
		];
		$itemException = new DT\Exception\InvalidData($violations);
		$exception = new DT\Exception\InvalidData([DT\Validator\Collection::INVALID_INNER => [10 => $violations]], $itemException);
		$valueStrategy->extract($source[0])->shouldBeCalledOnce()->willReturn(123);
		$valueStrategy->extract($source[1])->shouldBeCalledOnce()->willReturn(456);
		$valueStrategy->extract($source[10])->shouldBeCalledOnce()->willThrow($itemException);

		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null);
		$this->shouldThrow($exception)->during('extract', [$source]);
	}
}
