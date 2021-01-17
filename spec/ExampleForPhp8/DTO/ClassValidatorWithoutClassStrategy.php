<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

#[DTA\Validator(name: "testValidator")]
class ClassValidatorWithoutClassStrategy
{
}
