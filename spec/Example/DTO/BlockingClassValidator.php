<?php
declare(strict_types=1);

namespace spec\Example\DTO;

use Articus\DataTransfer\Annotation as DTA;

/**
 * @DTA\Strategy(name="testStrategy")
 * @DTA\Validator(name="testValidator", blocker=true)
 */
class BlockingClassValidator
{

}
