<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\EventSourcing\Contracts\FromVersion;
use Override;

final readonly class FromFirst implements FromVersion
{
    #[Override]
    public function lowerBoundExclusive(): int
    {
        return -1;
    }
}
