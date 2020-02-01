<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class NoArgObjectListSpec extends ObjectBehavior
{
	public function it_extracts_null(DT\Strategy\StrategyInterface $strategy)
	{
		$this->beConstructedWith($strategy, Example\DTO\Data::class);
		$this->extract(null)->shouldBe(null);
	}

	public function it_extracts_from_list_of_objects_of_valid_type(
		DT\Strategy\StrategyInterface $strategy,
		Example\DTO\Data $data1,
		Example\DTO\Data $data2
	)
	{
		$extractedData1 = ['test1' => 1];
		$extractedData2 = ['test2' => 2];
		$strategy->extract($data1)->shouldBeCalledOnce()->willReturn($extractedData1);
		$strategy->extract($data2)->shouldBeCalledOnce()->willReturn($extractedData2);

		$this->beConstructedWith($strategy, Example\DTO\Data::class);
		$this->extract([$data1, $data2])->shouldBe([$extractedData1, $extractedData2]);
	}

	public function it_throws_on_non_list(DT\Strategy\StrategyInterface $strategy, $data)
	{
		$this->beConstructedWith($strategy, Example\DTO\Data::class);
		$this->shouldThrow(\LogicException::class)->during('extract', [$data]);
	}

	public function it_throws_on_list_of_objects_of_invalid_type(DT\Strategy\StrategyInterface $strategy, $data)
	{
		$this->beConstructedWith($strategy, Example\DTO\Data::class);
		$this->shouldThrow(\LogicException::class)->during('extract', [[$data]]);
	}

	public function it_rethrows_wrapped_invalid_data_exception(DT\Strategy\StrategyInterface $strategy, Example\DTO\Data $data)
	{
		$violations = ['test' => 123];
		$innerError = new DT\Exception\InvalidData($violations);
		$strategy->extract($data)->shouldBeCalledOnce()->willThrow($innerError);
		$error = new DT\Exception\InvalidData([DT\Validator\Collection::INVALID_INNER => [$violations]], $innerError);

		$this->beConstructedWith($strategy, Example\DTO\Data::class);
		$this->shouldThrow($error)->during('extract', [[$data]]);
	}
}
