<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use JsonSerializable;

interface PrimitiveValueObject extends JsonSerializable
{
    public function __toString();
}
