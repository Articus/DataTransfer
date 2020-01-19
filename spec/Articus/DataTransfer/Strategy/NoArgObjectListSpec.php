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

	public function it_extracts_from_list_of_objects_of_valid_type(DT\Strategy\StrategyInterface $strategy, Example\DTO\Data $data)
	{
		$extractedData = ['test' => 123];
		$strategy->extract($data)->shouldBeCalledOnce()->willReturn($extractedData);

		$this->beConstructedWith($strategy, Example\DTO\Data::class);
		$this->extract([$data])->shouldBe([$extractedData]);
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
}
