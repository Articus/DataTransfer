<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Options;

class Chain
{
	/**
	 * Ordered list of validators
	 * @var ChainLink[]
	 */
	public array $links = [];

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'links':
					foreach ($value as $subKey => $subValue)
					{
						$this->links[$subKey] = ($subValue instanceof ChainLink) ? $subValue : new ChainLink($subValue);
					}
					break;
			}
		}
	}
}
