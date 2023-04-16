<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Options;

class Identifier
{
	/**
	 * Type of identifiable values
	 */
	public string $type;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'type':
					$this->type = $value;
					break;
			}
		}
	}
}
