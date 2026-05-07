<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\EventSourcing\StreamVersion;

interface ExpectedVersion
{
    /**
     * @throws \Meteia\EventSourcing\Exceptions\OptimisticConcurrencyFailure when the observed version does not satisfy the expectation
     */
    public function assertCompatibleWith(StreamVersion $observed): void;
}
