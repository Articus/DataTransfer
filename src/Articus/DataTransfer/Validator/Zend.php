<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

use Zend\Validator\ValidatorInterface as ZendValidator;

/**
 * "Bridge" that allows to check data with any Zend Validator
 */
class Zend implements ValidatorInterface
{
	/**
	 * @var ZendValidator
	 */
	protected $zendValidator;

	/**
	 * @param ZendValidator $zendValidator
	 */
	public function __construct(ZendValidator $zendValidator)
	{
		$this->zendValidator = $zendValidator;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if (!($data === null || $this->zendValidator->isValid($data)))
		{
			$result = $this->zendValidator->getMessages();
		}
		return $result;
	}
}
