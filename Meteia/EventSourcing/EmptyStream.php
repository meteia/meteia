<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\EventSourcing\Contracts\ExpectedVersion;
use Meteia\EventSourcing\Exceptions\OptimisticConcurrencyFailure;

final readonly class EmptyStream implements ExpectedVersion
{
    #[\Override]
    public function assertCompatibleWith(StreamVersion $observed): void
    {
        if (!$observed->equalTo(StreamVersion::start())) {
            throw new OptimisticConcurrencyFailure(StreamVersion::start(), $observed);
        }
    }
}
