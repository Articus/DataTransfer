<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Options;

class Collection
{
	/**
	 * Ordered list of validators that should be used on every collection item
	 * @var ChainLink[]
	 */
	public array $validators = [];

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'validators':
				case 'itemValidators':
				case 'item_validators':
					foreach ($value as $subKey => $subValue)
					{
						$this->validators[$subKey] = ($subValue instanceof ChainLink) ? $subValue : new ChainLink($subValue);
					}
					break;
			}
		}
	}
}
