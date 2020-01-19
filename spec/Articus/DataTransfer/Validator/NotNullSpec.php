<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class NotNullSpec extends ObjectBehavior
{
	public function it_denies_null()
	{
		$violations = [
			DT\Validator\NotNull::INVALID => 'Value should not be null.',
		];
		$this->validate(null)->shouldBe($violations);
	}

	public function it_allows_not_null()
	{
		$this->validate(false)->shouldBe([]);
		$this->validate(0)->shouldBe([]);
		$this->validate(0.0)->shouldBe([]);
		$this->validate('')->shouldBe([]);
		$this->validate([])->shouldBe([]);
		$this->validate(new \stdClass())->shouldBe([]);
	}
}
