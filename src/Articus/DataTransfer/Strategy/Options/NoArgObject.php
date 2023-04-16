<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy\Options;

use LogicException;
use function class_exists;
use function sprintf;

class NoArgObject
{
	/**
	 * Class name of object
	 * @var class-string
	 */
	public string $type;

	/**
	 * Name of the object metadata subset that should be used
	 */
	public string $subset = '';

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
			}
		}
	}
}
