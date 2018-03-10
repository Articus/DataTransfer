<?php

namespace Articus\DataTransfer\Validator;
use Articus\DataTransfer\Service;
use Zend\Validator\AbstractValidator;

/**
 * Validator that checks if value is an array and applies specific validators for its items with specific keys
 */
class Dictionary extends AbstractValidator
{
	const INVALID = 'objectInvalid';
	const INVALID_INNER = 'objectInvalidInner';

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $messageTemplates = [
		self::INVALID => 'Invalid type given. Associative array expected.',
	];

	/**
	 * @var Service
	 */
	protected $dataTransferService;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $subset;

	/**
	 * @param array $options
	 */
	public function __construct(array $options = null)
	{
		$type = isset($options['type'])? (string) $options['type'] : null;
		if (empty($type) || (!class_exists($type)))
		{
			throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
		}
		$this->type = $type;
		$this->subset = isset($options['subset'])? (string) $options['subset'] : '';
		parent::__construct();
	}

	/**
	 * @return Service
	 */
	public function getDataTransferService()
	{
		return $this->dataTransferService;
	}

	/**
	 * @param Service $dataTransferService
	 * @return Dictionary
	 */
	public function setDataTransferService(Service $dataTransferService = null)
	{
		$this->dataTransferService = $dataTransferService;
		return $this;
	}

	public function getValidator()
	{
		$metadata = $this->dataTransferService->getMetadataReader()->getMetadata($this->type, $this->subset);
		return $this->dataTransferService->getValidator($metadata);
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
			$messages = $this->getValidator()->validate($value);
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