<?php

namespace Articus\DataTransfer\Validator;

use Articus\DataTransfer\Annotation;
use Zend\Validator\AbstractValidator;
use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\ValidatorPluginManager;

/**
 * Validator that checks if value is an array and applies specified validators for each item of it
 */
class Collection extends AbstractValidator
{
	const INVALID = 'collectionInvalid';
	const INVALID_INNER = 'collectionInvalidInner';

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $messageTemplates = [
		self::INVALID => 'Invalid type given. Indexed array expected.',
	];

	/**
	 * @var ValidatorPluginManager
	 */
	protected $validatorPluginManager;

	/**
	 * @var array
	 */
	protected $validators;
	/**
	 * @var ValidatorChain
	 */
	protected $validatorChain;

	/**
	 * Collection constructor.
	 * @param array|null $options
	 */
	public function __construct(array $options = null)
	{
		$validators = isset($options['validators'])? $options['validators'] : null;
		if (empty($validators) || (!is_array($validators)))
		{
			throw new \InvalidArgumentException('No item validators.');
		}
		$this->validators = $validators;
		parent::__construct();
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
	public function setValidatorPluginManager($validatorPluginManager)
	{
		$this->validatorPluginManager = $validatorPluginManager;
		return $this;
	}


	protected function getValidatorChain()
	{
		if ($this->validatorChain === null)
		{
			$validatorChain = new ValidatorChain();
			$validatorChain->setPluginManager($this->validatorPluginManager);
			foreach ($this->validators as $validator)
			{
				switch (true)
				{
					case ($validator instanceof Annotation\Validator):
						$validatorChain->attachByName($validator->name, $validator->options, true, $validator->priority);
						break;
					case (is_array($validator) && isset($validator['name'])):
						$options = isset($validator['options'])? $validator['options'] : null;
						$priority = isset($validator['priority'])? $validator['priority'] : ValidatorChain::DEFAULT_PRIORITY;
						$validatorChain->attachByName($validator['name'], $options, true, $priority);
						break;
					case ($validator instanceof ValidatorInterface):
						$validatorChain->attach($validator, true);
						break;
					default:
						throw new \InvalidArgumentException('Invalid item validator.');
				}
			}
			$this->validatorChain = $validatorChain;
		}
		return $this->validatorChain;
	}

	public function isValid($value)
	{
		$result = false;
		$this->setValue($value);
		if (!is_array($value))
		{
			$this->error(self::INVALID);
		}
		else
		{
			$messages = [];
			foreach ($value as $index => $item)
			{
				if (!$this->getValidatorChain()->isValid($item))
				{
					$messages[$index] = $this->validatorChain->getMessages();
				}
			}
			if (empty($messages))
			{
				$result = true;
			}
			else
			{
				$this->abstractOptions['messages'][self::INVALID_INNER] = $messages;
			}
		}
		return $result;
	}
}