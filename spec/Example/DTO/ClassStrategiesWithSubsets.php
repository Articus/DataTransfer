<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

/**
 * @DTA\Strategy(name="testStrategy1", subset="testSubset1")
 * @DTA\Strategy(name="testStrategy2", subset="testSubset2")
 */
class ClassStrategiesWithSubsets
{
}
