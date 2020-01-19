<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

/**
 * Basic hydrator for untyped data (for example, the one you get from json_decode).
 */
class UntypedData implements HydratorInterface
{
	/**
	 * If source and destination are maps ("map" is either associative array or stdClass),
	 * source fields will be transfered into destination.
	 * Otherwise source will simply overwrite destination.
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		$isFromAssocArray = \is_array($from) && (\array_values($from) !== $from);
		$isFromStdClass = ($from instanceof \stdClass);
		$isToAssocArray = \is_array($to) && (\array_values($to) !== $to);
		$isToStdClass = ($to instanceof \stdClass);

		if (($isFromAssocArray || $isFromStdClass) && ($isToAssocArray || $isToStdClass))
		{
			foreach ($from as $key => $value)
			{
				if ($isToStdClass)
				{
					if (\property_exists($to, $key))
					{
						$this->hydrate($value, $to->{$key});
					}
					else
					{
						$to->{$key} = $value;
					}
				}
				else //if ($isToAssocArray)
				{
					if (\array_key_exists($key, $to))
					{
						$this->hydrate($value, $to[$key]);
					}
					else
					{
						$to[$key] = $value;
					}
				}
			}
		}
		else
		{
			$to = $from;
		}
	}
}
