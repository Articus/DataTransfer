<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class FieldDataSpec extends ObjectBehavior
{
	public function it_denies_data_that_is_not_key_value_map()
	{
		$data = 'test';
		$violations = [
			DT\Validator\FieldData::INVALID => 'Invalid data: expecting key-value map, not string.'
		];
		$this->beConstructedWith([]);
		$this->validate($data)->shouldBe($violations);
	}

	public function it_denies_key_value_map_with_invalid_fields(
		DT\Validator\ValidatorInterface $validator1,
		DT\Validator\ValidatorInterface $validator2,
		DT\Validator\ValidatorInterface $validator3,
		DT\Validator\ValidatorInterface $validator4,
		DT\Validator\ValidatorInterface $validator5
	)
	{
		$fieldName1 = 'test1';
		$fieldName2 = 'test2';
		$fieldName3 = 'test3';
		$fieldName4 = 'test4';
		$fieldName5 = 'test5';
		$data = [
			$fieldName1 => 1,
			$fieldName2 => 2,
			$fieldName3 => 3,
			$fieldName4 => 4,
			$fieldName5 => 5,
		];
		$violation2 = ['message2' => 22];
		$violation4 = ['message4' => 44];
		$violations = [
			DT\Validator\FieldData::INVALID_INNER => [
				$fieldName2 => $violation2,
				$fieldName4 => $violation4,
			],
		];

		$validator1->validate($data[$fieldName1])->shouldBeCalledOnce()->willReturn([]);
		$validator2->validate($data[$fieldName2])->shouldBeCalledOnce()->willReturn($violation2);
		$validator3->validate($data[$fieldName3])->shouldBeCalledOnce()->willReturn([]);
		$validator4->validate($data[$fieldName4])->shouldBeCalledOnce()->willReturn($violation4);
		$validator5->validate($data[$fieldName5])->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith([
			[$fieldName1, $validator1],
			[$fieldName2, $validator2],
			[$fieldName3, $validator3],
			[$fieldName4, $validator4],
			[$fieldName5, $validator5],
		]);
		$this->validate($data)->shouldBe($violations);
	}

	public function it_allows_key_value_map_with_unknown_fields()
	{
		$data = ['test' => 123];
		$this->beConstructedWith([]);
		$this->validate($data)->shouldBe([]);
	}

	public function it_allows_key_value_map_with_valid_fields(
		DT\Validator\ValidatorInterface $validator1,
		DT\Validator\ValidatorInterface $validator2
	)
	{
		$fieldName1 = 'test1';
		$fieldName2 = 'test2';
		$data = [
			$fieldName1 => 1,
			$fieldName2 => 2,
		];

		$validator1->validate($data[$fieldName1])->shouldBeCalledOnce()->willReturn([]);
		$validator2->validate($data[$fieldName2])->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith([
			[$fieldName1, $validator1],
			[$fieldName2, $validator2],
		]);
		$this->validate($data)->shouldBe([]);
	}
}
