<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\MetadataProvider;

use spec\Example;
use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

/**
 * TODO add expected text for LogicException's
 * TODO move check of NotNull validator priority for not nullable fields to separate example
 * TODO move check of FieldData validator priority for classes with fields to separate example
 */
class AnnotationSpec extends ObjectBehavior
{
	public function it_throws_if_there_is_no_class_metadata(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\NoClassMetadata::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$metadata = [[], [], [], [], []];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledTimes(5);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, 'test']);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, 'test']);
	}

	public function it_returns_cached_class_strategy(CacheStorage $cacheStorage)
	{
		$className = 'test\Class';
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = 'testSubset';
		$strategy = ['testStrategy', ['test' => 123]];
		$metadata = [
			[$subset => $strategy],
			[],
			[],
			[],
			[],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cacheStorage);
		$this->getClassStrategy($className, $subset)->shouldBe($strategy);
	}

	public function it_returns_class_strategy(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassStrategy::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$strategy = ['testStrategy', null];
		$metadata = [
			[$subset => $strategy],
			[$subset => [DT\Validator\Chain::class, ['links' => []]]],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassStrategy($className, $subset)->shouldBe($strategy);
	}

	public function it_returns_class_strategy_with_options(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassStrategyWithOptions::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$strategy = ['testStrategy', ['test' => 123]];
		$metadata = [
			[$subset => $strategy],
			[$subset => [DT\Validator\Chain::class, ['links' => []]]],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassStrategy($className, $subset)->shouldBe($strategy);
	}

	public function it_returns_class_strategy_with_specified_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassStrategiesWithSubsets::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset1 = 'testSubset1';
		$strategy1 = ['testStrategy1', null];
		$subset2 = 'testSubset2';
		$strategy2 = ['testStrategy2', null];
		$metadata = [
			[$subset1 => $strategy1, $subset2 => $strategy2],
			[$subset1 => [DT\Validator\Chain::class, ['links' => []]], $subset2 => [DT\Validator\Chain::class, ['links' => []]]],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassStrategy($className, $subset1)->shouldBe($strategy1);
	}

	public function it_throws_if_there_is_no_class_strategy_with_specified_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassStrategy::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$metadata = [
			[$subset => ['testStrategy', null]],
			[$subset => [DT\Validator\Chain::class, ['links' => []]]],
			[],
			[],
			[],
		];
		$unknownSubset = 'someSubset';

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $unknownSubset]);
	}

	public function it_throws_if_there_are_several_class_strategies_with_same_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassStrategiesWithSameSubset::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = 'testSubset';

		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(2)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
	}

	public function it_returns_cached_class_validator(CacheStorage $cacheStorage)
	{
		$className = 'test\Class';
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = 'testSubset';
		$validator = ['testValidator', ['test' => 123]];
		$metadata = [
			[],
			[$subset => $validator],
			[],
			[],
			[],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_empty_class_validator(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassStrategy::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$validator = [DT\Validator\Chain::class, ['links' => []]];
		$metadata = [
			[$subset => ['testStrategy', null]],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_class_validator(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassValidator::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$validator = [DT\Validator\Chain::class, ['links' => [['testValidator', null, false]]]];
		$metadata = [
			[$subset => ['testStrategy', null]],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_class_validator_with_options(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassValidatorWithOptions::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$validator = [DT\Validator\Chain::class, ['links' => [['testValidator', ['test' => 123], false]]]];
		$metadata = [
			[$subset => ['testStrategy', null]],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_blocking_class_validator(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\BlockingClassValidator::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$validator = [DT\Validator\Chain::class, ['links' => [['testValidator', null, true]]]];
		$metadata = [
			[$subset => ['testStrategy', null]],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_class_validator_with_specified_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassValidatorsWithSubsets::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset1 = 'testSubset1';
		$strategy1 = ['testStrategy1', null];
		$validator1 = [DT\Validator\Chain::class, ['links' => [['testValidator1', null, false]]]];
		$subset2 = 'testSubset2';
		$strategy2 = ['testStrategy2', null];
		$validator2 = [DT\Validator\Chain::class, ['links' => [['testValidator2', null, false]]]];
		$metadata = [
			[$subset1 => $strategy1, $subset2 => $strategy2],
			[$subset1 => $validator1, $subset2 => $validator2],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset1)->shouldBe($validator1);
	}

	public function it_returns_class_validators_with_same_subset_sorted_according_priority(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassValidatorsWithSameSubset::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';
		$strategy = ['testStrategy', null];
		$validator = [DT\Validator\Chain::class, ['links' => [
			['testValidator6', null, false],
			['testValidator3', null, false],
			['testValidator5', null, false],
			['testValidator1', null, false],
			['testValidator4', null, false],
			['testValidator2', null, false],
		]]];

		$metadata = [
			[$subset => $strategy],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_throws_on_class_validator_without_class_strategy(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassValidatorWithoutClassStrategy::class;
		$cacheKey = \str_replace('\\', '_', $className);
		$subset = '';

		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(2)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
	}

	public function it_returns_cached_class_field(CacheStorage $cacheStorage)
	{
		$className = 'test\Class';
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = 'testSubset';
		$fields = [['testField', ['test', false], ['setTest', true]]];
		$metadata = [
			[],
			[],
			[$subset => $fields],
			[],
			[],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_for_public_property(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\PublicClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], ['test', false]];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_for_protected_property(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ProtectedClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['getTest', true], ['setTest', true]];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_for_private_property(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\PrivateClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['getTest', true], ['setTest', true]];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_with_name(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithName::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'name';
		$field = [$fieldName, ['test', false], ['test', false]];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_throws_on_class_fields_with_same_name(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldsWithSameName::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_nullable_class_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\NullableClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], ['test', false]];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => []]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_with_getter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithGetter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['getName', true], ['test', false]];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_without_getter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithoutGetter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, null, ['test', false]];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_throws_on_class_field_with_absent_getter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithAbsentGetter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_nonpublic_getter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithNonpublicGetter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_getter_that_requires_parameter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithGetterThatRequiresParameter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_class_field_with_setter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithSetter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], ['setName', true]];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_without_setter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithoutSetter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], null];
		$fields = [$fieldName => $field];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => $fields],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_throws_on_class_field_with_absent_setter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithAbsentSetter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_nonpublic_setter(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithNonpublicSetter::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_setter_that_has_no_parameters(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithSetterThatHasNoParameters::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_setter_that_requires_two_parameters(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithSetterThatRequiresTwoParameters::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_class_field_with_specified_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithSubsets::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset1 = 'subset1';
		$subset2 = 'subset2';
		$fieldName1 = 'test1';
		$fieldName2 = 'test2';
		$field1 = [$fieldName1, ['test', false], ['test', false]];
		$fields1 = [$fieldName1 => $field1];
		$field2 = [$fieldName2, ['test', false], ['test', false]];
		$fields2 = [$fieldName2 => $field2];
		$metadata = [
			[
				$subset1 => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset1]],
				$subset2 => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset2]],
			],
			[
				$subset1 => [DT\Validator\Chain::class, ['links' => [
					[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset1], false]
				]]],
				$subset2 => [DT\Validator\Chain::class, ['links' => [
					[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset2], false]
				]]],
			],
			[
				$subset1 => $fields1,
				$subset2 => $fields2,
			],
			[
				$subset1 => [$fieldName1 => [DT\Strategy\Whatever::class, null]],
				$subset2 => [$fieldName2 => [DT\Strategy\Whatever::class, null]],
			],
			[
				$subset1 => [$fieldName1 => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]],
				$subset2 => [$fieldName2 => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]],
			],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassFields($className, $subset1)->shouldIterateAs($fields1);
	}

	public function it_throws_if_property_has_several_class_fields_with_same_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldsWithSameSubsetForSameProperty::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_cached_field_strategy(CacheStorage $cacheStorage)
	{
		$className = 'test\Class';
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = 'testSubset';
		$fieldName = 'testField';
		$fieldStrategy = ['testStrategy', ['test' => 123]];
		$metadata = [
			[],
			[],
			[],
			[$subset => [$fieldName => $fieldStrategy]],
			[],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cacheStorage);
		$this->getFieldStrategy($className, $subset, $fieldName)->shouldBe($fieldStrategy);
	}

	public function it_returns_default_field_strategy(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\PublicClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], ['test', false]];
		$fieldStrategy = [DT\Strategy\Whatever::class, null];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => $fieldStrategy]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldStrategy($className, $subset, $fieldName)->shouldBe($fieldStrategy);
	}

	public function it_returns_field_strategy(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldStrategy::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldStrategy = ['testStrategy', null];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => $fieldStrategy]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldStrategy($className, $subset, $fieldName)->shouldBe($fieldStrategy);
	}

	public function it_returns_field_strategy_with_options(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldStrategyWithOptions::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldStrategy = ['testStrategy', ['test' => 123]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => $fieldStrategy]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldStrategy($className, $subset, $fieldName)->shouldBe($fieldStrategy);
	}

	public function it_returns_field_strategy_with_specified_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldStrategiesWithSubsets::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset1 = 'subset1';
		$subset2 = 'subset2';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldStrategy1 = ['testStrategy1', null];
		$fieldStrategy2 = ['testStrategy2', null];
		$metadata = [
			[
				$subset1 => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset1]],
				$subset2 => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset2]],
			],
			[
				$subset1 => [DT\Validator\Chain::class, ['links' => [
					[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset1], false]]
				]],
				$subset2 => [DT\Validator\Chain::class, ['links' => [
					[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset2], false]]
				]],
			],
			[
				$subset1 => [$fieldName => $field],
				$subset2 => [$fieldName => $field],
			],
			[
				$subset1 => [$fieldName => $fieldStrategy1],
				$subset2 => [$fieldName => $fieldStrategy2],
			],
			[
				$subset1 => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]],
				$subset2 => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]],
			],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldStrategy($className, $subset1, $fieldName)->shouldBe($fieldStrategy1);
	}

	public function it_throws_if_property_has_several_field_strategies_with_same_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldStrategiesWithSameSubsetForSameProperty::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_if_property_has_field_strategy_but_is_not_class_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldStrategyWithoutClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'testField';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_cached_field_validator(CacheStorage $cacheStorage)
	{
		$className = 'test\Class';
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = 'testSubset';
		$fieldName = 'testField';
		$fieldValidator = ['testValidator', ['test' => 123]];
		$metadata = [
			[],
			[],
			[],
			[],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cacheStorage);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_default_field_validator_for_not_nullable_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\PublicClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], ['test', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_empty_field_validator_for_nullable_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\NullableClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], ['test', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => []]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_field_validator(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldValidator::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, null, true],
			['testValidator', null, false],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_field_validator_with_options(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldValidatorWithOptions::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, null, true],
			['testValidator', ['test' => 123], false],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_blocking_field_validator(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\BlockingFieldValidator::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, null, true],
			['testValidator', null, true],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_field_validator_with_specified_subset(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldValidatorsWithSubsets::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset1 = 'subset1';
		$subset2 = 'subset2';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator1 = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, null, true],
			['testValidator1', null, false],
		]]];
		$fieldValidator2 = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, null, true],
			['testValidator2', null, false],
		]]];
		$metadata = [
			[
				$subset1 => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset1]],
				$subset2 => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset2]],
			],
			[
				$subset1 => [DT\Validator\Chain::class, ['links' => [
					[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset1], false]
				]]],
				$subset2 => [DT\Validator\Chain::class, ['links' => [
					[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset2], false]
				]]],
			],
			[
				$subset1 => [$fieldName => $field],
				$subset2 => [$fieldName => $field],
			],
			[
				$subset1 => [$fieldName => [DT\Strategy\Whatever::class, null]],
				$subset2 => [$fieldName => [DT\Strategy\Whatever::class, null]],
			],
			[
				$subset1 => [$fieldName => $fieldValidator1],
				$subset2 => [$fieldName => $fieldValidator2],
			],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldValidator($className, $subset1, $fieldName)->shouldBe($fieldValidator1);
	}

	public function it_returns_field_validators_with_same_subset_sorted_according_priority(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldValidatorsWithSameSubset::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [
			['testValidator7', null, false],
			[DT\Validator\NotNull::class, null, true],
			['testValidator6', null, false],
			['testValidator3', null, false],
			['testValidator5', null, false],
			['testValidator1', null, false],
			['testValidator4', null, false],
			['testValidator2', null, false],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_throws_if_property_has_field_validator_but_is_not_class_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\FieldValidatorWithoutClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'testField';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_default_class_strategy_if_there_is_class_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\PublicClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$strategy = [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]];
		$metadata = [
			[$subset => $strategy],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => [$fieldName, ['test', false], ['test', false]]]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassStrategy($className, $subset)->shouldBe($strategy);
	}

	public function it_returns_default_class_validator_if_there_is_class_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\PublicClassField::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$validator = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => $validator],
			[$subset => [$fieldName => [$fieldName, ['test', false], ['test', false]]]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_throws_if_there_is_class_strategy_and_there_is_class_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithClassStrategy::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$cacheStorage->getItem($cacheKey)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cacheStorage);
		$this->shouldThrow(\LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(\LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(\LogicException::class)->during('current');
		$this->shouldThrow(\LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(\LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_class_validators_and_default_class_validator_if_there_is_class_field(CacheStorage $cacheStorage)
	{
		$className = Example\DTO\ClassFieldWithClassValidator::class;
		$cacheKey = \str_replace('\\', '_', $className);;
		$subset = '';
		$fieldName = 'test';
		$validator = [DT\Validator\Chain::class, ['links' => [
			['testValidator2', null, false],
			[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false],
			['testValidator1', null, false],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => $validator],
			[$subset => [$fieldName => [$fieldName, ['test', false], ['test', false]]]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, null]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, null, true]]]]]],
		];
		$cacheStorage->getItem($cacheKey)->shouldBeCalledOnce()->willReturn(null);
		$cacheStorage->setItem($cacheKey, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cacheStorage);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}
}
