<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

#[DTA\Strategy(name: "testStrategy1", subset: "testSubset")]
#[DTA\Strategy(name: "testStrategy2", subset: "testSubset")]
class ClassStrategiesWithSameSubset
{
}
