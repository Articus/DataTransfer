<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

#[DTA\Strategy(name: "testStrategy")]
#[DTA\Validator(name: "testValidator1")]
#[DTA\Validator(name: "testValidator2", priority: 0)]
#[DTA\Validator(name: "testValidator3", priority: 2)]
#[DTA\Validator(name: "testValidator4", priority: 1)]
#[DTA\Validator(name: "testValidator5", priority: 2)]
#[DTA\Validator(name: "testValidator6", priority: 3)]
class ClassValidatorsWithSameSubset
{
}
