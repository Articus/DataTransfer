<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Exception;

use Exception;
use Throwable;

/**
 * Exception to throw during extraction/merge/hydration as validation "kill switch".
 * For example when data for merge is manged so badly that there is no point to start its validation
 * or when data validation is too expensive to be done separately from its hydration.
 */
class InvalidData extends Exception
{
	public const DEFAULT_VIOLATION = ['invalidData' => 'Invalid data'];

	/**
	 * List of reasons why data validation failed
	 */
	protected array $violations;

	public function __construct(array $violations = self::DEFAULT_VIOLATION, Throwable $previous = null)
	{
		parent::__construct('Invalid data', 0, $previous);
		$this->violations = $violations;
	}

	public function getViolations(): array
	{
		return $this->violations;
	}
}
