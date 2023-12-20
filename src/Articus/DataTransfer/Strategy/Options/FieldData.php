<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Options;

use LogicException;
use function class_exists;
use function sprintf;

class FieldData
{
	/**
	 * Name of the class where transfer metadata  is declared
	 * @var class-string
	 */
	public string $type;

	/**
	 * Name of the transfer metadata subset that should be used
	 */
	public string $subset = '';

	/**
	 * Flag if untyped data should be extracted as stdClass. If not, it is extracted as associative array.
	 */
	public bool $extractStdClass = false;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'type':
					if (!class_exists($value))
					{
						throw new LogicException(sprintf('Type "%s" does not exist', $value));
					}
					$this->type = $value;
					break;
				case 'subset':
					$this->subset = $value;
					break;
				case 'extractStdClass':
				case 'extract_std_class':
					$this->extractStdClass = $value;
					break;
			}
		}
	}
}
