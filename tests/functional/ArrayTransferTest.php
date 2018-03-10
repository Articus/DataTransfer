<?php

namespace Test\DataTransfer;

use Articus\DataTransfer\Service;

class ArrayTransferTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Test\DataTransfer\FunctionalTester
	 */
	protected $tester;

	protected function _before()
	{
	}

	protected function _after()
	{
	}

	public function testScalarCopy()
	{
		$a = [
			'existed' => 'a',
			'unchanged' => 'a',
		];
		$b = [
			'new' => 'b',
			'existed' => 'b',
		];
		$result = Service::arrayTransfer($a, $b);
		$expectedResult = [
			'new' => 'b',
			'existed' => 'b',
			'unchanged' => 'a',
		];
		$this->tester->assertEquals($result, $expectedResult);
	}

	public function testListCopy()
	{
		$a = [
			'existed_1' => ['a','a'],
			'existed_empty_1' => [],
			'existed_2' => ['a','a'],
			'existed_empty_2' => [],
			'unchanged' => ['a','a'],
			'unchanged_empty' => [],
		];
		$b = [
			'new' => ['b','b'],
			'new_empty' => [],
			'existed_1' => ['b', 'b'],
			'existed_empty_1' => ['b', 'b'],
			'existed_2' => [],
			'existed_empty_2' => [],
		];
		$result = Service::arrayTransfer($a, $b);
		$expectedResult = [
			'new' => ['b','b'],
			'new_empty' => [],
			'existed_1' => ['b','b'],
			'existed_empty_1' => ['b','b'],
			'existed_2' => [],
			'existed_empty_2' => [],
			'unchanged' => ['a','a'],
			'unchanged_empty' => [],
		];
		$this->tester->assertEquals($result, $expectedResult);
	}

	public function testDictionaryCopy()
	{
		$a = [
			'existed_1' => [
				'existed' => 'a',
				'unchanged' => 'a',
			],
			'existed_empty_1' => [],
			'existed_2' => [
				'existed' => 'a',
				'unchanged' => 'a',
			],
			'existed_empty_2' => [],
			'unchanged' => [
				'existed' => 'a',
				'unchanged' => 'a',
			],
			'unchanged_empty' => [],
		];
		$b = [
			'new' => [
				'new' => 'b',
			],
			'new_empty' => [],
			'existed_1' => [
				'new' => 'b',
				'existed' => 'b',
			],
			'existed_empty_1' => [
				'new' => 'b',
			],
			'existed_2' => [],
			'existed_empty_2' => [],
		];
		$result = Service::arrayTransfer($a, $b);
		$expectedResult = [
			'new' => [
				'new' => 'b',
			],
			'new_empty' => [],
			'existed_1' => [
				'new' => 'b',
				'existed' => 'b',
				'unchanged' => 'a',
			],
			'existed_empty_1' => [
				'new' => 'b',
			],
			'existed_2' => [
				'existed' => 'a',
				'unchanged' => 'a',
			],
			'existed_empty_2' => [],
			'unchanged' => [
				'existed' => 'a',
				'unchanged' => 'a',
			],
			'unchanged_empty' => [],
		];
		$this->tester->assertEquals($result, $expectedResult);
	}
}