<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IdentifiableValueSpec extends ObjectBehavior
{
	public function it_extracts_null(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier,
		Example\InvokableInterface $typedValueConstructor,
		Example\InvokableInterface $untypedValueConstructor
	)
	{
		$valueStrategy->extract(Argument::any())->shouldNotBeCalled();

		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, $typedValueConstructor, $untypedValueConstructor);
		$this->extract(null)->shouldBeNull();
	}

	public function it_delegates_non_null_extraction_to_value_strategy(
		DT\Strategy\StrategyInterface $valueStrategy,
		Example\InvokableInterface $typedValueIdentifier,
		Example\InvokableInterface $untypedValueIdentifier,
		Example\InvokableInterface $typedValueConstructor,
		Example\InvokableInterface $untypedValueConstructor
	)
	{
		$source = new \stdClass();
		$destination = new \stdClass();

		$valueStrategy->extract($source)->shouldBeCalledOnce()->willReturn($destination);

		$this->beConstructedWith($valueStrategy, $typedValueIdentifier, $untypedValueIdentifier, $typedValueConstructor, $untypedValueConstructor);
		$this->extract($source)->shouldBe($destination);
	}
}
