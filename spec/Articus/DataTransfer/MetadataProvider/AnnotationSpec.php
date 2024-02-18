<?php
declare(strict_types=1);

namespace spec\Articus\DataTransfer\MetadataProvider;

use Articus\DataTransfer as DT;
use LogicException;
use PhpSpec\ObjectBehavior;
use spec\Example;

/**
 * TODO add expected text for LogicException's
 * TODO move check of NotNull validator priority for not nullable fields to separate example
 * TODO move check of FieldData validator priority for classes with fields to separate example
 */
class AnnotationSpec extends ObjectBehavior
{
	public function it_throws_if_there_is_no_class_metadata(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\NoClassMetadata::class;
		$subset = '';
		$metadata = [[], [], [], [], []];

		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledTimes(5);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, 'test']);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, 'test']);
	}

	public function it_returns_cached_class_strategy(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = 'test\Class';
		$subset = 'testSubset';
		$strategy = ['testStrategy', ['test' => 123]];
		$metadata = [
			[$subset => $strategy],
			[],
			[],
			[],
			[],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cache);
		$this->getClassStrategy($className, $subset)->shouldBe($strategy);
	}

	public function it_returns_class_strategy(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassStrategy::class;
		$subset = '';
		$strategy = ['testStrategy', []];
		$metadata = [
			[$subset => $strategy],
			[$subset => [DT\Validator\Chain::class, ['links' => []]]],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassStrategy($className, $subset)->shouldBe($strategy);
	}

	public function it_returns_class_strategy_with_options(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassStrategyWithOptions::class;
		$subset = '';
		$strategy = ['testStrategy', ['test' => 123]];
		$metadata = [
			[$subset => $strategy],
			[$subset => [DT\Validator\Chain::class, ['links' => []]]],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassStrategy($className, $subset)->shouldBe($strategy);
	}

	public function it_returns_class_strategy_with_specified_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassStrategiesWithSubsets::class;
		$subset1 = 'testSubset1';
		$strategy1 = ['testStrategy1', []];
		$subset2 = 'testSubset2';
		$strategy2 = ['testStrategy2', []];
		$metadata = [
			[$subset1 => $strategy1, $subset2 => $strategy2],
			[$subset1 => [DT\Validator\Chain::class, ['links' => []]], $subset2 => [DT\Validator\Chain::class, ['links' => []]]],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassStrategy($className, $subset1)->shouldBe($strategy1);
	}

	public function it_throws_if_there_is_no_class_strategy_with_specified_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassStrategy::class;
		$subset = '';
		$metadata = [
			[$subset => ['testStrategy', []]],
			[$subset => [DT\Validator\Chain::class, ['links' => []]]],
			[],
			[],
			[],
		];
		$unknownSubset = 'someSubset';

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $unknownSubset]);
	}

	public function it_throws_if_there_are_several_class_strategies_with_same_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassStrategiesWithSameSubset::class;
		$subset = 'testSubset';

		$cache->get($className)->shouldBeCalledTimes(2)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
	}

	public function it_returns_cached_class_validator(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = 'test\Class';
		$subset = 'testSubset';
		$validator = ['testValidator', ['test' => 123]];
		$metadata = [
			[],
			[$subset => $validator],
			[],
			[],
			[],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_empty_class_validator(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassStrategy::class;
		$subset = '';
		$validator = [DT\Validator\Chain::class, ['links' => []]];
		$metadata = [
			[$subset => ['testStrategy', []]],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_class_validator(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassValidator::class;
		$subset = '';
		$validator = [DT\Validator\Chain::class, ['links' => [['testValidator', [], false]]]];
		$metadata = [
			[$subset => ['testStrategy', []]],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_class_validator_with_options(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassValidatorWithOptions::class;
		$subset = '';
		$validator = [DT\Validator\Chain::class, ['links' => [['testValidator', ['test' => 123], false]]]];
		$metadata = [
			[$subset => ['testStrategy', []]],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_blocking_class_validator(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\BlockingClassValidator::class;
		$subset = '';
		$validator = [DT\Validator\Chain::class, ['links' => [['testValidator', [], true]]]];
		$metadata = [
			[$subset => ['testStrategy', []]],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_returns_class_validator_with_specified_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassValidatorsWithSubsets::class;
		$subset1 = 'testSubset1';
		$strategy1 = ['testStrategy1', []];
		$validator1 = [DT\Validator\Chain::class, ['links' => [['testValidator1', [], false]]]];
		$subset2 = 'testSubset2';
		$strategy2 = ['testStrategy2', []];
		$validator2 = [DT\Validator\Chain::class, ['links' => [['testValidator2', [], false]]]];
		$metadata = [
			[$subset1 => $strategy1, $subset2 => $strategy2],
			[$subset1 => $validator1, $subset2 => $validator2],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset1)->shouldBe($validator1);
	}

	public function it_returns_class_validators_with_same_subset_sorted_according_priority(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassValidatorsWithSameSubset::class;
		$subset = '';
		$strategy = ['testStrategy', []];
		$validator = [DT\Validator\Chain::class, ['links' => [
			['testValidator6', [], false],
			['testValidator3', [], false],
			['testValidator5', [], false],
			['testValidator1', [], false],
			['testValidator4', [], false],
			['testValidator2', [], false],
		]]];

		$metadata = [
			[$subset => $strategy],
			[$subset => $validator],
			[],
			[],
			[],
		];

		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_throws_on_class_validator_without_class_strategy(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassValidatorWithoutClassStrategy::class;
		$subset = '';

		$cache->get($className)->shouldBeCalledTimes(2)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
	}

	public function it_returns_cached_class_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = 'test\Class';
		$subset = 'testSubset';
		$fields = [['testField', ['test', false], ['setTest', true]]];
		$metadata = [
			[],
			[],
			[$subset => $fields],
			[],
			[],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_for_public_property(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\PublicClassField::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_for_protected_property(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ProtectedClassField::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_for_private_property(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\PrivateClassField::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_with_name(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithName::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_throws_on_class_fields_with_same_name(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldsWithSameName::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_nullable_class_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\NullableClassField::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => []]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_with_getter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithGetter::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_without_getter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithoutGetter::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_throws_on_class_field_with_absent_getter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithAbsentGetter::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_nonpublic_getter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithNonpublicGetter::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_getter_that_requires_parameter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithGetterThatRequiresParameter::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_class_field_with_setter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithSetter::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_returns_class_field_without_setter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithoutSetter::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset)->shouldIterateAs($fields);
	}

	public function it_throws_on_class_field_with_absent_setter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithAbsentSetter::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_nonpublic_setter(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithNonpublicSetter::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_setter_that_has_no_parameters(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithSetterThatHasNoParameters::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_on_class_field_with_setter_that_requires_two_parameters(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithSetterThatRequiresTwoParameters::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_class_field_with_specified_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithSubsets::class;
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
				$subset1 => [$fieldName1 => [DT\Strategy\Whatever::class, []]],
				$subset2 => [$fieldName2 => [DT\Strategy\Whatever::class, []]],
			],
			[
				$subset1 => [$fieldName1 => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]],
				$subset2 => [$fieldName2 => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]],
			],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassFields($className, $subset1)->shouldIterateAs($fields1);
	}

	public function it_throws_if_property_has_several_class_fields_with_same_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldsWithSameSubsetForSameProperty::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_cached_field_strategy(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = 'test\Class';
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
		$cache->get($className)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cache);
		$this->getFieldStrategy($className, $subset, $fieldName)->shouldBe($fieldStrategy);
	}

	public function it_returns_default_field_strategy(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\PublicClassField::class;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], ['test', false]];
		$fieldStrategy = [DT\Strategy\Whatever::class, []];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => $fieldStrategy]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldStrategy($className, $subset, $fieldName)->shouldBe($fieldStrategy);
	}

	public function it_returns_field_strategy(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldStrategy::class;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldStrategy = ['testStrategy', []];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => $fieldStrategy]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldStrategy($className, $subset, $fieldName)->shouldBe($fieldStrategy);
	}

	public function it_returns_field_strategy_with_options(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldStrategyWithOptions::class;
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
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldStrategy($className, $subset, $fieldName)->shouldBe($fieldStrategy);
	}

	public function it_returns_field_strategy_with_specified_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldStrategiesWithSubsets::class;
		$subset1 = 'subset1';
		$subset2 = 'subset2';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldStrategy1 = ['testStrategy1', []];
		$fieldStrategy2 = ['testStrategy2', []];
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
				$subset1 => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]],
				$subset2 => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]],
			],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldStrategy($className, $subset1, $fieldName)->shouldBe($fieldStrategy1);
	}

	public function it_throws_if_property_has_several_field_strategies_with_same_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldStrategiesWithSameSubsetForSameProperty::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_throws_if_property_has_field_strategy_but_is_not_class_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldStrategyWithoutClassField::class;
		$subset = '';
		$fieldName = 'testField';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_cached_field_validator(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = 'test\Class';
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
		$cache->get($className)->shouldBeCalledOnce()->willReturn($metadata);

		$this->beConstructedWith($cache);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_default_field_validator_for_not_nullable_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\PublicClassField::class;
		$subset = '';
		$fieldName = 'test';
		$field = [$fieldName, ['test', false], ['test', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_empty_field_validator_for_nullable_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\NullableClassField::class;
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
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_field_validator(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldValidator::class;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, [], true],
			['testValidator', [], false],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_field_validator_with_options(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldValidatorWithOptions::class;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, [], true],
			['testValidator', ['test' => 123], false],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_blocking_field_validator(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\BlockingFieldValidator::class;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, [], true],
			['testValidator', [], true],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_returns_field_validator_with_specified_subset(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldValidatorsWithSubsets::class;
		$subset1 = 'subset1';
		$subset2 = 'subset2';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator1 = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, [], true],
			['testValidator1', [], false],
		]]];
		$fieldValidator2 = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\NotNull::class, [], true],
			['testValidator2', [], false],
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
				$subset1 => [$fieldName => [DT\Strategy\Whatever::class, []]],
				$subset2 => [$fieldName => [DT\Strategy\Whatever::class, []]],
			],
			[
				$subset1 => [$fieldName => $fieldValidator1],
				$subset2 => [$fieldName => $fieldValidator2],
			],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldValidator($className, $subset1, $fieldName)->shouldBe($fieldValidator1);
	}

	public function it_returns_field_validators_with_same_subset_sorted_according_priority(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldValidatorsWithSameSubset::class;
		$subset = '';
		$fieldName = 'testField';
		$field = [$fieldName, ['testField', false], ['testField', false]];
		$fieldValidator = [DT\Validator\Chain::class, ['links' => [
			['testValidator7', [], false],
			[DT\Validator\NotNull::class, [], true],
			['testValidator6', [], false],
			['testValidator3', [], false],
			['testValidator5', [], false],
			['testValidator1', [], false],
			['testValidator4', [], false],
			['testValidator2', [], false],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => $field]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => $fieldValidator]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getFieldValidator($className, $subset, $fieldName)->shouldBe($fieldValidator);
	}

	public function it_throws_if_property_has_field_validator_but_is_not_class_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\FieldValidatorWithoutClassField::class;
		$subset = '';
		$fieldName = 'testField';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_default_class_strategy_if_there_is_class_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\PublicClassField::class;
		$subset = '';
		$fieldName = 'test';
		$strategy = [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]];
		$metadata = [
			[$subset => $strategy],
			[$subset => [DT\Validator\Chain::class, ['links' => [
				[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]]
			]]],
			[$subset => [$fieldName => [$fieldName, ['test', false], ['test', false]]]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassStrategy($className, $subset)->shouldBe($strategy);
	}

	public function it_returns_default_class_validator_if_there_is_class_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\PublicClassField::class;
		$subset = '';
		$fieldName = 'test';
		$validator = [DT\Validator\Chain::class, ['links' => [
			[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false]
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => $validator],
			[$subset => [$fieldName => [$fieldName, ['test', false], ['test', false]]]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}

	public function it_throws_if_there_is_class_strategy_and_there_is_class_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithClassStrategy::class;
		$subset = '';
		$fieldName = 'test';
		$cache->get($className)->shouldBeCalledTimes(5)->willReturn(null);

		$this->beConstructedWith($cache);
		$this->shouldThrow(LogicException::class)->during('getClassStrategy', [$className, $subset]);
		$this->shouldThrow(LogicException::class)->during('getClassValidator', [$className, $subset]);
		$this->getClassFields($className, $subset)->shouldThrow(LogicException::class)->during('current');
		$this->shouldThrow(LogicException::class)->during('getFieldStrategy', [$className, $subset, $fieldName]);
		$this->shouldThrow(LogicException::class)->during('getFieldValidator', [$className, $subset, $fieldName]);
	}

	public function it_returns_class_validators_and_default_class_validator_if_there_is_class_field(DT\MetadataCache\MetadataCacheInterface $cache)
	{
		$className = Example\DTO\ClassFieldWithClassValidator::class;
		$subset = '';
		$fieldName = 'test';
		$validator = [DT\Validator\Chain::class, ['links' => [
			['testValidator2', [], false],
			[DT\Validator\FieldData::class, ['type' => $className, 'subset' => $subset], false],
			['testValidator1', [], false],
		]]];
		$metadata = [
			[$subset => [DT\Strategy\FieldData::class, ['type' => $className, 'subset' => $subset]]],
			[$subset => $validator],
			[$subset => [$fieldName => [$fieldName, ['test', false], ['test', false]]]],
			[$subset => [$fieldName => [DT\Strategy\Whatever::class, []]]],
			[$subset => [$fieldName => [DT\Validator\Chain::class, ['links' => [[DT\Validator\NotNull::class, [], true]]]]]],
		];
		$cache->get($className)->shouldBeCalledOnce()->willReturn(null);
		$cache->set($className, $metadata)->shouldBeCalledOnce();

		$this->beConstructedWith($cache);
		$this->getClassValidator($className, $subset)->shouldBe($validator);
	}
}
