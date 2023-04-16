<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Validator\Options;

class ChainLink
{
	/**
	 * Validator plugin name
	 */
	public string $name;

	/**
	 * Validator plugin options
	 */
	public array $options = [];

	/**
	 * Flag if validator is blocker (@see \Articus\DataTransfer\Validator\Chain for details)
	 */
	public bool $blocker = false;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case '0':
				case 'name':
					$this->name = $value;
					break;
				case '1':
				case 'options':
					$this->options = $value;
					break;
				case '2':
				case 'blocker':
					$this->blocker = $value;
					break;
			}
		}
	}
}
