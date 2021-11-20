<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Strategy;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class IdentifierSpec extends ObjectBehavior
{
	public function it_extracts_from_null(DT\IdentifiableValueLoader $loader)
	{
		$this->beConstructedWith($loader, 'test');
		$this->extract(null)->shouldBeNull();
	}

	public function it_extracts_from_value_without_identifier(DT\IdentifiableValueLoader $loader, $value)
	{
		$type = 'test';
		$loader->identify($type, $value)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($loader, $type);
		$this->extract($value)->shouldBeNull();
	}

	public function it_extracts_from_value_with_identifier(DT\IdentifiableValueLoader $loader, $value)
	{
		$type = 'test';
		$id = 123;
		$loader->identify($type, $value)->shouldBeCalledOnce()->willReturn($id);
		$loader->bank($type, $value)->shouldBeCalledOnce();

		$this->beConstructedWith($loader, $type);
		$this->extract($value)->shouldBe($id);
	}
}
