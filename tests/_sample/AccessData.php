<?php
namespace Test\DataTransfer\Sample;

use Articus\DataTransfer\Annotation as DTA;

class AccessData
{
	/**
	 * @DTA\Data()
	 */
	public $property;

	/**
	 * @DTA\Data()
	 */
	protected $propertyWithAccessors;

	public function getPropertyWithAccessors()
	{
		return $this->propertyWithAccessors;
	}

	public function setPropertyWithAccessors($propertyWithAccessors)
	{
		$this->propertyWithAccessors = $propertyWithAccessors;
		return $this;
	}

	/**
	 * @DTA\Data(getter="customGetAccessor", setter="customSetAccessor")
	 */
	protected $propertyWithCustomAccessors;

	public function customGetAccessor()
	{
		return $this->propertyWithCustomAccessors;
	}

	public function customSetAccessor($propertyWithCustomAccessors)
	{
		$this->propertyWithCustomAccessors = $propertyWithCustomAccessors;
		return $this;
	}

	/**
	 * @DTA\Data(getter="", nullable=true)
	 */
	protected $propertyWithoutGetter;

	public function getPropertyWithoutGetter()
	{
		return $this->propertyWithoutGetter;
	}

	public function setPropertyWithoutGetter($propertyWithoutGetter)
	{
		$this->propertyWithoutGetter = $propertyWithoutGetter;
		return $this;
	}

	/**
	 * @DTA\Data(setter="")
	 */
	protected $propertyWithoutSetter;

	public function getPropertyWithoutSetter()
	{
		return $this->propertyWithoutSetter;
	}

	public function setPropertyWithoutSetter($propertyWithoutSetter)
	{
		$this->propertyWithoutSetter = $propertyWithoutSetter;
		return $this;
	}
}