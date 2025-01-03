<?php
declare(strict_types=1);

use Articus\DataTransfer as DT;
use spec\Example;

describe(DT\Validator\SerializableValue::class, function ()
{
	describe('->validate', function ()
	{
		afterEach(function ()
		{
			Mockery::close();
		});
		it('allows null', function ()
		{
			$valueValidator = mock(DT\Validator\ValidatorInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$validator = new DT\Validator\SerializableValue($valueValidator, $unserializer);
			expect($validator->validate(null))->toBe([]);
		});
		it('denies non-string', function ()
		{
			$error = [DT\Validator\SerializableValue::INVALID => 'Invalid data: expecting string.'];
			$valueValidator = mock(DT\Validator\ValidatorInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$validator = new DT\Validator\SerializableValue($valueValidator, $unserializer);
			expect($validator->validate(true))->toBe($error);
			expect($validator->validate(123))->toBe($error);
			expect($validator->validate(123.456))->toBe($error);
			expect($validator->validate([]))->toBe($error);
			expect($validator->validate(new stdClass()))->toBe($error);
		});
		it('denies invalid string', function ()
		{
			$data = 'some data';
			$dataError = ['aaa' => 111];
			$error = [DT\Validator\SerializableValue::INVALID_INNER => $dataError];
			$exception = new DT\Exception\InvalidData($dataError);
			$valueValidator = mock(DT\Validator\ValidatorInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$unserializer->shouldReceive('__invoke')->with($data)->andThrow($exception)->once();

			$validator = new DT\Validator\SerializableValue($valueValidator, $unserializer);
			expect($validator->validate($data))->toBe($error);
		});
		it('denies string with invalid serialized value', function ()
		{
			$data = 'some data';
			$value = mock();
			$valueError = ['aaa' => 111];
			$error = [DT\Validator\SerializableValue::INVALID_INNER => $valueError];
			$valueValidator = mock(DT\Validator\ValidatorInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$unserializer->shouldReceive('__invoke')->with($data)->andReturn($value)->once();
			$valueValidator->shouldReceive('validate')->with($value)->andReturn($valueError)->once();

			$validator = new DT\Validator\SerializableValue($valueValidator, $unserializer);
			expect($validator->validate($data))->toBe($error);
		});
		it('allows string with valid serialized value', function ()
		{
			$data = 'some data';
			$value = mock();
			$valueValidator = mock(DT\Validator\ValidatorInterface::class);
			$unserializer = mock(Example\InvokableInterface::class);

			$unserializer->shouldReceive('__invoke')->with($data)->andReturn($value)->once();
			$valueValidator->shouldReceive('validate')->with($value)->andReturn([])->once();

			$validator = new DT\Validator\SerializableValue($valueValidator, $unserializer);
			expect($validator->validate($data))->toBe([]);
		});
	});
});
