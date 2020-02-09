<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator;

use PhpSpec\ObjectBehavior;
use Laminas\Validator\ValidatorInterface as LaminasValidator;

class LaminasSpec extends ObjectBehavior
{
	public function it_allows_null(LaminasValidator $validator)
	{
		$validator->isValid()->shouldNotBeCalled();
		$this->beConstructedWith($validator);
		$this->validate(null)->shouldBe([]);
	}

	public function it_allows_data_that_is_valid_according_zend_validator(LaminasValidator $validator, $data)
	{
		$validator->isValid($data)->shouldBeCalledOnce()->willReturn(true);
		$this->beConstructedWith($validator);
		$this->validate($data)->shouldBe([]);
	}

	public function it_denies_data_that_is_not_valid_according_zend_validator(LaminasValidator $validator, $data)
	{
		$violations = ['test' => 123];
		$validator->isValid($data)->shouldBeCalledOnce()->willReturn(false);
		$validator->getMessages()->shouldBeCalledOnce()->willReturn($violations);
		$this->beConstructedWith($validator);
		$this->validate($data)->shouldBe($violations);
	}
}
