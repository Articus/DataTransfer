<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\Validator;

use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;
use stdClass;

class IdentifierSpec extends ObjectBehavior
{
	public function it_allows_null(DT\IdentifiableValueLoader $loader)
	{
		$this->beConstructedWith($loader, 'test');
		$this->validate(null)->shouldBe([]);
	}

	public function it_allows_integer_identifier_with_value(DT\IdentifiableValueLoader $loader, $value)
	{
		$type = 'test';
		$id = 123;
		$loader->get($type, $id)->shouldBeCalledOnce()->willReturn($value);

		$this->beConstructedWith($loader, $type);
		$this->validate($id)->shouldBe([]);
	}

	public function it_allows_string_identifier_with_value(DT\IdentifiableValueLoader $loader, $value)
	{
		$type = 'test';
		$id = 'abc';
		$loader->get($type, $id)->shouldBeCalledOnce()->willReturn($value);

		$this->beConstructedWith($loader, $type);
		$this->validate($id)->shouldBe([]);
	}

	public function it_denies_non_integer_and_non_string(DT\IdentifiableValueLoader $loader)
	{
		$violations = [DT\Validator\Identifier::INVALID => 'Invalid identifier - expecting integer or string.'];
		$this->beConstructedWith($loader, 'test');

		$this->validate(true)->shouldBe($violations);
		$this->validate(123.456)->shouldBe($violations);
		$this->validate([])->shouldBe($violations);
		$this->validate(new stdClass())->shouldBe($violations);
	}

	public function it_denies_integer_identifier_without_value(DT\IdentifiableValueLoader $loader)
	{
		$violations = [DT\Validator\Identifier::UNKNOWN => 'Unknown identifier.'];
		$type = 'test';
		$id = 123;

		$loader->get($type, $id)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($loader, $type);
		$this->validate($id)->shouldBe($violations);
	}

	public function it_denies_string_identifier_without_value(DT\IdentifiableValueLoader $loader)
	{
		$violations = [DT\Validator\Identifier::UNKNOWN => 'Unknown identifier.'];
		$type = 'test';
		$id = 'abc';

		$loader->get($type, $id)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($loader, $type);
		$this->validate($id)->shouldBe($violations);
	}
}
