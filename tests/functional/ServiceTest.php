<?php
namespace Test\DataTransfer;


use Articus\DataTransfer\Service;
use Articus\DataTransfer\ServiceFactory;
use Articus\DataTransfer\Validator;
use Interop\Container\ContainerInterface;

class ServiceTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Test\DataTransfer\FunctionalTester
	 */
	protected $tester;

	/**
	 * @var Service
	 */
	protected $service;

	protected function _before()
	{
		$config = [
			Service::class => [
				'metadata_cache' => [
					'adapter' => 'memory',
				],
				'validators' => [
					'factories' => [
						Validator\Dictionary::class => Validator\Factory::class,
						Validator\Collection::class => Validator\Factory::class,
					],
					'aliases' => [
						'Dictionary' => Validator\Dictionary::class,
						'Collection' => Validator\Collection::class,
					],
					'invokables' => [
						'ClassValidator' => Sample\ClassValidator::class,
						'SubsetClassValidator' => Sample\SubsetClassValidator::class,
					],
				],
				'strategies' => [
					'invokables' => [
						'PrefixStrategy' => Sample\PrefixStrategy::class,
					],
				],
			],
		];
		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get('config')->willReturn($config);
		$containerProphecy->has('MvcTranslator')->willReturn(false);
		$container = $containerProphecy->reveal();

		$factory = new ServiceFactory();
		$this->service = $factory($container, Service::class);
		$containerProphecy->get(Service::class)->willReturn($this->service);
	}

	protected function _after()
	{
	}

	// tests
	public function testDataTransferForPropertiesWithDistinctAccessType()
	{
		$from = new Sample\AccessData();
		$from->property = 'propertyValueFrom';
		$from
			->setPropertyWithAccessors('propertyWithAccessorsValueFrom')
			->customSetAccessor('propertyWithCustomAccessorsValueFrom')
			->setPropertyWithoutGetter('propertyWithoutGetterValueFrom')
			->setPropertyWithoutSetter('propertyWithoutSetterValueFrom')
		;

		$to = new Sample\AccessData();
		$to->property = 'propertyValueTo';
		$to
			->setPropertyWithAccessors('propertyWithAccessorsValueTo')
			->customSetAccessor('propertyWithCustomAccessorsValueTo')
			->setPropertyWithoutGetter('propertyWithoutGetterValueTo')
			->setPropertyWithoutSetter('propertyWithoutSetterValueTo')
		;

		$messages = $this->service->transfer($from, $to);
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');

		$result = new Sample\AccessData();
		$result->property = 'propertyValueFrom';
		$result
			->setPropertyWithAccessors('propertyWithAccessorsValueFrom')
			->customSetAccessor('propertyWithCustomAccessorsValueFrom')
			->setPropertyWithoutGetter(null)
			->setPropertyWithoutSetter('propertyWithoutSetterValueTo')
		;
		$this->tester->assertEquals($result, $to);
    }

	public function testDataTransferForPropertiesWithStrategies()
	{
		$from = new Sample\ValidateData();
		$from->scalar = '0a';
		$from->scalarArray = ['0a', '1b', '2c'];
		$from->object = new Sample\EmbeddedData();
		$from->object->property = '0a';
		$from->objectArray = [new Sample\EmbeddedData(), new Sample\EmbeddedData(), new Sample\EmbeddedData()];
		$from->objectArray[0]->property = '0a';
		$from->objectArray[1]->property = '1b';
		$from->objectArray[2]->property = '2c';

		$to = new Sample\ValidateData();

		$messages = $this->service->transfer($from, $to);
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$this->assertEquals($from, $to);
	}


	public function testDataTransferForPropertiesWithValidators()
	{
		$data = [];
		$object = new Sample\ValidateData();
		$originalObject = clone $object;
		$messages = $this->service->transfer($data, $object);

		$this->assertArrayHasPath($messages, 'scalar.isEmpty');
		$this->assertArrayHasPath($messages, 'scalarArray.isEmpty');
		$this->assertArrayHasPath($messages, 'object.isEmpty');
		$this->assertArrayHasPath($messages, 'objectArray.isEmpty');

		foreach (['nullableScalar', 'nullableScalarArray', 'nullableObject', 'nullableObjectArray'] as $validKey)
		{
			$this->tester->assertArrayNotHasKey($validKey, $messages);
		}
		$this->tester->assertEquals($originalObject, $object);

		$data = [
			'object' => [],
			'nullableObject' => [],
			'objectArray' => [[], []],
			'nullableObjectArray' => [[], []],
		];
		$messages = $this->service->transfer($data, $object);

		$this->assertArrayHasPath($messages, 'scalar.isEmpty');
		$this->assertArrayHasPath($messages, 'scalarArray.isEmpty');
		foreach (['nullableScalar', 'nullableScalarArray'] as $validKey)
		{
			$this->tester->assertArrayNotHasKey($validKey, $messages);
		}
		$this->assertArrayHasPath($messages, 'object.objectInvalidInner.property.isEmpty');
		$this->tester->assertArrayNotHasKey('nullableProperty', $messages['object']['objectInvalidInner']);
		$this->assertArrayHasPath($messages, 'nullableObject.objectInvalidInner.property.isEmpty');
		$this->tester->assertArrayNotHasKey('nullableProperty', $messages['nullableObject']['objectInvalidInner']);
		$this->assertArrayHasPath($messages, 'objectArray.collectionInvalidInner.0.objectInvalidInner.property.isEmpty');
		$this->tester->assertArrayNotHasKey('nullableProperty', $messages['objectArray']['collectionInvalidInner'][0]['objectInvalidInner']);
		$this->assertArrayHasPath($messages, 'objectArray.collectionInvalidInner.1.objectInvalidInner.property.isEmpty');
		$this->tester->assertArrayNotHasKey('nullableProperty', $messages['objectArray']['collectionInvalidInner'][1]['objectInvalidInner']);
		$this->assertArrayHasPath($messages, 'nullableObjectArray.collectionInvalidInner.0.objectInvalidInner.property.isEmpty');
		$this->tester->assertArrayNotHasKey('nullableProperty', $messages['nullableObjectArray']['collectionInvalidInner'][0]['objectInvalidInner']);
		$this->assertArrayHasPath($messages, 'nullableObjectArray.collectionInvalidInner.1.objectInvalidInner.property.isEmpty');
		$this->tester->assertArrayNotHasKey('nullableProperty', $messages['nullableObjectArray']['collectionInvalidInner'][1]['objectInvalidInner']);
		$this->tester->assertEquals($originalObject, $object);

		$data = [
			'scalar' => '',
			'nullableScalar' => 'value',
			'scalarArray' => ['0a', '', '0a', 'value', '0a'],
			'nullableScalarArray' => ['0a', '', '0a', 'value', '0a'],
			'object' => ['property' => ''],
			'nullableObject' => ['property' => 'value'],
			'objectArray' => [['property' => '0a'], null, ['property' => ''], ['property' => '0a'], ['property' => 'value'], ['property' => '0a']],
			'nullableObjectArray' => [['property' => '0a'], null, ['property' => ''], ['property' => '0a'], ['property' => 'value'], ['property' => '0a']],
		];
		$messages = $this->service->transfer($data, $object);

		$this->assertArrayHasPath($messages, 'scalar.stringLengthTooShort');
		$this->assertArrayHasPath($messages, 'nullableScalar.notHex');

		$this->assertArrayHasPath($messages, 'scalarArray.collectionInvalidInner.1.stringLengthTooShort');
		$this->assertArrayHasPath($messages, 'scalarArray.collectionInvalidInner.3.notHex');
		$this->assertArrayHasPath($messages, 'nullableScalarArray.collectionInvalidInner.1.stringLengthTooShort');
		$this->assertArrayHasPath($messages, 'nullableScalarArray.collectionInvalidInner.3.notHex');

		$this->assertArrayHasPath($messages, 'object.objectInvalidInner.property.stringLengthTooShort');
		$this->assertArrayHasPath($messages, 'nullableObject.objectInvalidInner.property.notHex');

		$this->assertArrayHasPath($messages, 'objectArray.collectionInvalidInner.1.objectInvalid');
		$this->assertArrayHasPath($messages, 'objectArray.collectionInvalidInner.2.objectInvalidInner.property.stringLengthTooShort');
		$this->assertArrayHasPath($messages, 'objectArray.collectionInvalidInner.4.objectInvalidInner.property.notHex');

		$this->assertArrayHasPath($messages, 'nullableObjectArray.collectionInvalidInner.1.objectInvalid');
		$this->assertArrayHasPath($messages, 'nullableObjectArray.collectionInvalidInner.2.objectInvalidInner.property.stringLengthTooShort');
		$this->assertArrayHasPath($messages, 'nullableObjectArray.collectionInvalidInner.4.objectInvalidInner.property.notHex');

		$this->tester->assertEquals($originalObject, $object);
	}

	public function testDataTransferForPropertiesWithDistinctFieldName()
	{
		$object = new Sample\RenameData();
		$object->property = 'test';
		$data = [];
		$messages = $this->service->transfer($object, $data);
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$result = ['field' => 'test'];
		$this->tester->assertEquals($result, $data);

		$data = ['field' => 'test'];
		$object = new Sample\RenameData();
		$messages = $this->service->transfer($data, $object);
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$result =  new Sample\RenameData();
		$result->property = 'test';
		$this->tester->assertEquals($result, $object);
	}

	public function testDataTransferWithClassValidator()
	{
		$data = [];
		$object = new Sample\ClassValidateData();
		$messages = $this->service->transfer($data, $object);
		$this->assertArrayHasPath($messages, '*.invalidClass');

		$data = ['a' => 'test'];
		$object = new Sample\ClassValidateData();
		$messages = $this->service->transfer($data, $object);
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$result = new Sample\ClassValidateData();
		$result->a = 'test';
		$this->tester->assertEquals($result, $object);

		$data = ['b' => 'test'];
		$object = new Sample\ClassValidateData();
		$messages = $this->service->transfer($data, $object);
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$result = new Sample\ClassValidateData();
		$result->b = 'test';
		$this->tester->assertEquals($result, $object);
	}

	public function testDataTransferForSubsets()
	{
		$data = [];
		$object = new Sample\SubsetData();
		$exception = new \LogicException('No metadata for subset "test" in class Test\DataTransfer\Sample\SubsetData.');
		$this->tester->expectException($exception, function() use (&$data, &$object)
		{
			$this->service->transfer($data, $object, null, '', 'test');
		});
		$this->tester->expectException($exception, function() use (&$data, &$object)
		{
			$this->service->transfer($object, $data, null, 'test', '');
		});

		$object = new Sample\SubsetData();
		$object->a = 123;
		$object->b = 321;
		$object->ab = 123;
		$data = [];
		$messages = $this->service->transfer($object, $data, null, 'a');
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$result = ['a' => 'testA-123', 'ab' => 'testA-123'];
		$this->tester->assertEquals($result, $data);

		$object = new Sample\SubsetData();
		$object->a = 123;
		$object->b = 321;
		$object->ab = 321;
		$data = [];
		$messages = $this->service->transfer($object, $data, null, 'b');
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$result = ['b' => 'testB-321', 'ab' => 'testB-321'];
		$this->tester->assertEquals($result, $data);

		$data = ['a' => 'testA-123', 'ab' => 'testA-123'];
		$object = new Sample\SubsetData();
		$messages = $this->service->transfer($data, $object, null, '', 'a');
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$result = new Sample\SubsetData();
		$result->a = 123;
		$result->ab = 123;
		$this->tester->assertEquals($result, $object);

		$data = ['b' => 'testB-321', 'ab' => 'testB-321'];
		$object = new Sample\SubsetData();
		$messages = $this->service->transfer($data, $object, null, '', 'b');
		$this->tester->assertIsEmpty($messages, 'Failed to transfer data.');
		$result = new Sample\SubsetData();
		$result->b = 321;
		$result->ab = 321;
		$this->tester->assertEquals($result, $object);

		$data = ['a' => null, 'ab' => 'wrong value'];
		$object = new Sample\SubsetData();
		$messages = $this->service->transfer($data, $object, null, '', 'a');
		$result = [
			'a' => ['isEmpty' => 'Value is required and can\'t be empty'],
			'ab' => ['regexNotMatch' => 'The input does not match against pattern \'/^testA\\-\\d+$/\''],
			'*' => ['invalidSubsetClass' => 'Properties "ab" and "a" should equal.'],
		];
		$this->tester->assertEquals($result, $messages);

		$data = ['b' => null, 'ab' => 'wrong value'];
		$object = new Sample\SubsetData();
		$messages = $this->service->transfer($data, $object, null, '', 'b');
		$result = [
			'b' => ['isEmpty' => 'Value is required and can\'t be empty'],
			'ab' => ['regexNotMatch' => 'The input does not match against pattern \'/^testB\\-\\d+$/\''],
			'*' => ['invalidSubsetClass' => 'Properties "ab" and "b" should equal.'],
		];
		$this->tester->assertEquals($result, $messages);
	}

	protected function assertArrayHasPath($array, $path, $delimiter = '.')
	{
		$parts = explode($delimiter, $path);
		$arrayLink = &$array;
		foreach ($parts as $part)
		{
			$this->tester->assertInternalType('array', $arrayLink);
			$this->tester->assertArrayHasKey($part, $arrayLink);
			$arrayLink = &$arrayLink[$part];
		}
	}
}