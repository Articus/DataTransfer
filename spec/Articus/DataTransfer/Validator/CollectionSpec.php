<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class CollectionSpec extends ObjectBehavior
{
	public function it_allows_null(DT\Validator\ValidatorInterface $itemValidator)
	{
		$data = null;
		$itemValidator->validate($data)->shouldNotBeCalled();
		$this->beConstructedWith($itemValidator);
		$this->validate($data)->shouldBe([]);
	}

	public function it_denies_data_that_is_not_iterable_collection(DT\Validator\ValidatorInterface $itemValidator)
	{
		$data = 'test';
		$violations = [
			DT\Validator\Collection::INVALID => 'Invalid data: expecting iterable collection, not string.'
		];
		$itemValidator->validate($data)->shouldNotBeCalled();
		$this->beConstructedWith($itemValidator);
		$this->validate($data)->shouldBe($violations);
	}

	public function it_denies_iterable_collection_with_invalid_items(DT\Validator\ValidatorInterface $itemValidator)
	{
		$data = [1, 2, 3, 4, 5];
		$itemViolation1 = ['test1' => 11];
		$itemViolation3 = ['test3' => 33];
		$violations = [
			DT\Validator\Collection::INVALID_INNER => [
				1 => $itemViolation1,
				3 => $itemViolation3,
			]
		];
		$itemValidator->validate($data[0])->shouldBeCalledOnce()->willReturn([]);
		$itemValidator->validate($data[1])->shouldBeCalledOnce()->willReturn($itemViolation1);
		$itemValidator->validate($data[2])->shouldBeCalledOnce()->willReturn([]);
		$itemValidator->validate($data[3])->shouldBeCalledOnce()->willReturn($itemViolation3);
		$itemValidator->validate($data[4])->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($itemValidator);
		$this->validate($data)->shouldBe($violations);
	}

	public function it_allows_empty_iterable_collection(DT\Validator\ValidatorInterface $itemValidator)
	{
		$data = [];
		$itemValidator->validate($data)->shouldNotBeCalled();
		$this->beConstructedWith($itemValidator);
		$this->validate($data)->shouldBe([]);
	}

	public function it_allows_iterable_collection_with_valid_items(DT\Validator\ValidatorInterface $itemValidator)
	{
		$data = [1, 2];
		$itemValidator->validate($data[0])->shouldBeCalledOnce()->willReturn([]);
		$itemValidator->validate($data[1])->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($itemValidator);
		$this->validate($data)->shouldBe([]);
	}
}
