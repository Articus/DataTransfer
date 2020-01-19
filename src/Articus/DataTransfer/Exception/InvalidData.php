<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Exception;

/**
 * Exception to throw during hydration/extraction when validation is too expensive to be done separately
 */
class InvalidData extends \Exception
{
	/**
	 * List of reasons why data validation failed
	 * @var array
	 */
	protected $violations;

	public function __construct(array $violations, \Throwable $previous = null)
	{
		parent::__construct('Invalid data', 0, $previous);
		$this->violations = $violations;
	}

	/**
	 * @return array
	 */
	public function getViolations(): array
	{
		return $this->violations;
	}
}
