<?php

declare(strict_types=1);

namespace Meteia\Yeso\Stubs;

use Meteia\Yeso\ValueObjects\ImmutableArray;
use stdClass;

class ImmutableArrayStub extends ImmutableArray
{
    public const TYPE = stdClass::class;
}
