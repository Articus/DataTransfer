<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class TypeCompliantSpec extends ObjectBehavior
{
	public function it_allows_null(DT\Validator\ValidatorInterface $validator)
	{
		$this->beConstructedWith($validator);
		$this->validate(null)->shouldBe([]);
	}

	public function it_validates_with_type_validation_rule(DT\Validator\ValidatorInterface $validator, $data)
	{
		$violations = ['test' => 123];
		$validator->validate($data)->shouldBeCalledOnce()->willReturn($violations);
		$this->beConstructedWith($validator);
		$this->validate($data)->shouldBe($violations);
	}
}
