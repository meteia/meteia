<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

interface FromVersion
{
    /**
     * Lower bound (exclusive) of the stream version range to read.
     * Returns -1 to mean "from the very first event in the stream".
     */
    public function lowerBoundExclusive(): int;
}
