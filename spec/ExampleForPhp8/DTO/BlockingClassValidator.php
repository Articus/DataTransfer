<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\DTO;

use Articus\DataTransfer\PhpAttribute as DTA;

#[DTA\Strategy(name: "testStrategy")]
#[DTA\Validator(name: "testValidator", blocker: true)]
class BlockingClassValidator
{

}
