<?php
namespace Test\DataTransfer\Sample;

use Articus\DataTransfer\Annotation as DTA;

/**
 * @DTA\Validator(name="SubsetClassValidator", options={"prop":"a"}, subset="a")
 * @DTA\Validator(name="SubsetClassValidator", options={"prop":"b"}, subset="b")
 */
class SubsetData
{
	/**
	 * @DTA\Data(subset="a")
	 * @DTA\Validator(name="Regex", options={"pattern":"/^testA\-\d+$/"}, subset="a")
	 * @DTA\Strategy(name="PrefixStrategy", options={"prefix":"testA"}, subset="a")
	 * @var int
	 */
	public $a;
	/**
	 * @DTA\Data(subset="b")
	 * @DTA\Validator(name="Regex", options={"pattern":"/^testB\-\d+$/"}, subset="b")
	 * @DTA\Strategy(name="PrefixStrategy", options={"prefix":"testB"}, subset="b")
	 * @var int
	 */
	public $b;
	/**
	 * @DTA\Data(subset="a")
	 * @DTA\Validator(name="Regex", options={"pattern":"/^testA\-\d+$/"}, subset="a")
	 * @DTA\Strategy(name="PrefixStrategy", options={"prefix":"testA"}, subset="a")
	 * @DTA\Data(subset="b")
	 * @DTA\Validator(name="Regex", options={"pattern":"/^testB\-\d+$/"}, subset="b")
	 * @DTA\Strategy(name="PrefixStrategy", options={"prefix":"testB"}, subset="b")
	 * @var int
	 */
	public $ab;
}