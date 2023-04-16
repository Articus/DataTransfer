<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator;

use Articus\DataTransfer\IdentifiableValueLoader;
use function is_int;
use function is_string;

class Identifier implements ValidatorInterface
{
	public const INVALID = 'identifierInvalid';
	public const UNKNOWN = 'identifierUnknown';

	protected IdentifiableValueLoader $loader;

	protected string $type;

	public function __construct(IdentifiableValueLoader $loader, string $type)
	{
		$this->loader = $loader;
		$this->type = $type;
	}

	/**
	 * @inheritDoc
	 */
	public function validate($data): array
	{
		$result = [];
		if ($data !== null)
		{
			if (is_int($data) || is_string($data))
			{
				$value = $this->loader->get($this->type, $data);
				if ($value === null)
				{
					$result[self::UNKNOWN] = 'Unknown identifier.';
				}
			}
			else
			{
				$result[self::INVALID] = 'Invalid identifier - expecting integer or string.';
			}
		}
		return $result;
	}
}
