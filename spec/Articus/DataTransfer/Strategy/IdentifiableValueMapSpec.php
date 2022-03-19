<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class IdentifiableValueMapSpec extends ObjectBehavior
{
	public function it_throws_on_extract_from_non_iterable_and_non_stdclass(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null, false);
		$this->shouldThrow(DT\Exception\InvalidData::class)->during('extract', [null]);
		$this->shouldThrow(DT\Exception\InvalidData::class)->during('extract', [1]);
		$this->shouldThrow(DT\Exception\InvalidData::class)->during('extract', [new class(){}]);
	}

	public function it_extracts_empty_array_from_empty_source(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null, false);
		$this->extract([])->shouldBe([]);
		$this->extract(new \stdClass())->shouldBe([]);
		$this->extract(new \ArrayObject())->shouldBe([]);
	}

	public function it_extracts_empty_stdclass_from_empty_source(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null, true);

		$this->extract([])->shouldBeLike(new \stdClass());
		$this->extract(new \stdClass())->shouldBeLike(new \stdClass());
		$this->extract(new \ArrayObject())->shouldBeLike(new \stdClass);
	}

	public function it_extracts_non_empty_array_from_non_empty_source_using_value_strategy(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$sourceArray = [
			'test1' => 'qwe',
			'test2' => 'asd',
			'test3' => 'zxc',
		];
		$sourceStdClass = new \stdClass();
		$sourceStdClass->test1 = 'rty';
		$sourceStdClass->test2 = 'fgh';
		$sourceStdClass->test3 = 'vbn';
		$destination = [
			'test1' => 123,
			'test2' => 456,
			'test3' => 789,
		];
		$valueStrategy->extract($sourceArray['test1'])->willReturn($destination['test1'])->shouldBeCalledOnce();
		$valueStrategy->extract($sourceArray['test2'])->willReturn($destination['test2'])->shouldBeCalledOnce();
		$valueStrategy->extract($sourceArray['test3'])->willReturn($destination['test3'])->shouldBeCalledOnce();
		$valueStrategy->extract($sourceStdClass->test1)->willReturn($destination['test1'])->shouldBeCalledOnce();
		$valueStrategy->extract($sourceStdClass->test2)->willReturn($destination['test2'])->shouldBeCalledOnce();
		$valueStrategy->extract($sourceStdClass->test3)->willReturn($destination['test3'])->shouldBeCalledOnce();

		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null, false);
		$this->extract($sourceArray)->shouldBe($destination);
		$this->extract($sourceStdClass)->shouldBe($destination);
	}

	public function it_extracts_non_empty_stdclass_from_non_empty_source_using_value_strategy(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$sourceArray = [
			'test1' => 'qwe',
			'test2' => 'asd',
			'test3' => 'zxc',
		];
		$sourceStdClass = new \stdClass();
		$sourceStdClass->test1 = 'rty';
		$sourceStdClass->test2 = 'fgh';
		$sourceStdClass->test3 = 'vbn';
		$destination = new \stdClass();
		$destination->test1 = 123;
		$destination->test2 = 456;
		$destination->test3 = 789;
		$valueStrategy->extract($sourceArray['test1'])->willReturn($destination->test1)->shouldBeCalledOnce();
		$valueStrategy->extract($sourceArray['test2'])->willReturn($destination->test2)->shouldBeCalledOnce();
		$valueStrategy->extract($sourceArray['test3'])->willReturn($destination->test3)->shouldBeCalledOnce();
		$valueStrategy->extract($sourceStdClass->test1)->willReturn($destination->test1)->shouldBeCalledOnce();
		$valueStrategy->extract($sourceStdClass->test2)->willReturn($destination->test2)->shouldBeCalledOnce();
		$valueStrategy->extract($sourceStdClass->test3)->willReturn($destination->test3)->shouldBeCalledOnce();

		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null, true);
		$this->extract($sourceArray)->shouldBeLike($destination);
		$this->extract($sourceStdClass)->shouldBeLike($destination);
	}

	public function it_wraps_and_rethrows_invalid_data_exceptions_from_value_strategy(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier
	)
	{
		$source = [
			'test1' => 12,
			'test2' => 34,
			'test3' => 56,
			'test4' => 78,
		];
		$violations = [
			'test' => 12345,
		];
		$itemException = new DT\Exception\InvalidData($violations);
		$exception = new DT\Exception\InvalidData([DT\Validator\Collection::INVALID_INNER => ['test3' => $violations]], $itemException);
		$valueStrategy->extract($source['test1'])->shouldBeCalledOnce()->willReturn(123);
		$valueStrategy->extract($source['test2'])->shouldBeCalledOnce()->willReturn(456);
		$valueStrategy->extract($source['test3'])->shouldBeCalledOnce()->willThrow($itemException);

		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, null, null, null, false);
		$this->shouldThrow($exception)->during('extract', [$source]);
	}
}
