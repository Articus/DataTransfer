<?php

namespace Articus\DataTransfer;

use Zend\Validator\NotEmpty;
use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\ValidatorPluginManager;

class Validator
{
	const GLOBAL_VALIDATOR_KEY = '*';
	/**
	 * @var Metadata
	 */
	protected $metadata;

	/**
	 * @var ValidatorPluginManager
	 */
	protected $validatorPluginManager;

	/**
	 * Internal validator cache
	 * @var ValidatorInterface[]
	 */
	protected $validators = [];

	/**
	 * @return Metadata
	 */
	public function getMetadata()
	{
		return $this->metadata;
	}

	/**
	 * @param Metadata $metadata
	 * @return self
	 */
	public function setMetadata(Metadata $metadata = null)
	{
		$this->metadata = $metadata;
		return $this;
	}

	/**
	 * @return ValidatorPluginManager
	 */
	public function getValidatorPluginManager()
	{
		return $this->validatorPluginManager;
	}

	/**
	 * @param ValidatorPluginManager $validatorPluginManager
	 * @return self
	 */
	public function setValidatorPluginManager(ValidatorPluginManager $validatorPluginManager = null)
	{
		$this->validatorPluginManager = $validatorPluginManager;
		return $this;
	}

	/**
	 * Validates associative array according metadata
	 * @param array $array
	 * @return array
	 */
	public function validate(array $array)
	{
		$messages = [];
		foreach ($this->metadata->fields as $field)
		{
			if (array_key_exists($field, $array))
			{
				if (!(($array[$field] === null) && $this->metadata->nullables[$field]))
				{
					$validator = $this->getValidator($field);
					if (!$validator->isValid($array[$field]))
					{
						$messages[$field] = $validator->getMessages();
					}
				}
			}
		}
		$globalValidator = $this->getValidator(self::GLOBAL_VALIDATOR_KEY);
		if (!$globalValidator->isValid($array))
		{
			$messages[self::GLOBAL_VALIDATOR_KEY] = $globalValidator->getMessages();
		}
		return $messages;
	}

	/**
	 * Builds validator chain for specified field according metadata
	 * @param string $field
	 * @return ValidatorInterface
	 */
	protected function getValidator($field)
	{
		$result = null;
		if (isset($this->validators[$field]))
		{
			$result = $this->validators[$field];
		}
		else
		{
			$result = new ValidatorChain();
			$result->setPluginManager($this->validatorPluginManager);
			if (isset($this->metadata->validators[$field]))
			{
				foreach ($this->metadata->validators[$field] as $validator)
				{
					/** @var $validator Annotation\Validator */
					$result->attachByName($validator->name, $validator->options, true, $validator->priority);
				}
			}
			if (($field !== self::GLOBAL_VALIDATOR_KEY) && ($this->metadata->nullables[$field] === false))
			{
				$result->attachByName(NotEmpty::class, ['type' => NotEmpty::NULL], true, 10000);
			}
			$this->validators[$field] = $result;
		}
		return $result;
	}

}
