<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class ChainSpec extends ObjectBehavior
{
	public function it_validates_without_links($data)
	{
		$this->beConstructedWith([]);
		$this->validate($data)->shouldBe([]);
	}

	public function it_validates_with_each_link(
		DT\Validator\ValidatorInterface $validator1,
		DT\Validator\ValidatorInterface $validator2,
		$data
	)
	{
		$links = [
			[$validator1, false],
			[$validator2, false],
		];
		$violation1 = ['test1' => 1];
		$violation2 = ['test2' => 2];
		$violations = \array_merge_recursive($violation1, $violation2);
		$validator1->validate($data)->shouldBeCalledOnce()->willReturn($violation1);
		$validator2->validate($data)->shouldBeCalledOnce()->willReturn($violation2);

		$this->beConstructedWith($links);
		$this->validate($data)->shouldBe($violations);
	}

	public function it_validates_with_each_link_if_blocking_validation_rule_is_not_violated(
		DT\Validator\ValidatorInterface $validator1,
		DT\Validator\ValidatorInterface $validator2,
		DT\Validator\ValidatorInterface $validator3,
		$data
	)
	{
		$links = [
			[$validator1, false],
			[$validator2, true],
			[$validator3, false],
		];
		$violation1 = ['test1' => 1];
		$violation2 = [];
		$violation3 = ['test3' => 3];
		$violations = \array_merge_recursive($violation1, $violation2, $violation3);
		$validator1->validate($data)->shouldBeCalledOnce()->willReturn($violation1);
		$validator2->validate($data)->shouldBeCalledOnce()->willReturn($violation2);
		$validator3->validate($data)->shouldBeCalledOnce()->willReturn($violation3);

		$this->beConstructedWith($links);
		$this->validate($data)->shouldBe($violations);
	}

	public function it_validates_with_each_link_till_first_violated_blocking_validation_rule(
		DT\Validator\ValidatorInterface $validator1,
		DT\Validator\ValidatorInterface $validator2,
		DT\Validator\ValidatorInterface $validator3,
		$data
	)
	{
		$links = [
			[$validator1, false],
			[$validator2, true],
			[$validator3, false],
		];
		$violation1 = ['test1' => 1];
		$violation2 = ['test2' => 2];
		$violations = \array_merge_recursive($violation1, $violation2);
		$validator1->validate($data)->shouldBeCalledOnce()->willReturn($violation1);
		$validator2->validate($data)->shouldBeCalledOnce()->willReturn($violation2);
		$validator3->validate($data)->shouldNotBeCalled();

		$this->beConstructedWith($links);
		$this->validate($data)->shouldBe($violations);
	}
}
