<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator;

use PhpSpec\ObjectBehavior;
use stdClass;

class WhateverSpec extends ObjectBehavior
{
	public function it_allows_anything()
	{
		$this->validate(null)->shouldBe([]);
		$this->validate(false)->shouldBe([]);
		$this->validate(0)->shouldBe([]);
		$this->validate(0.0)->shouldBe([]);
		$this->validate('')->shouldBe([]);
		$this->validate([])->shouldBe([]);
		$this->validate(new stdClass())->shouldBe([]);
	}
}
