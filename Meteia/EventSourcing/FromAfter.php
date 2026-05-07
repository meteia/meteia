<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\EventSourcing\Contracts\FromVersion;

final readonly class FromAfter implements FromVersion
{
    public function __construct(
        private StreamVersion $version,
    ) {}

    #[\Override]
    public function lowerBoundExclusive(): int
    {
        return $this->version->asInt();
    }
}
