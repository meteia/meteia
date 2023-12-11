<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects;

use Meteia\ValueObjects\Identity\UniqueId;

abstract readonly class AggregateRootId extends UniqueId
{
}
