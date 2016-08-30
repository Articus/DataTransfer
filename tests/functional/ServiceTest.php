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
			'data_transfer' => [
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