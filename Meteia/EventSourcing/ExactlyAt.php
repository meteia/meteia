<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\EventSourcing\Contracts\ExpectedVersion;
use Meteia\EventSourcing\Exceptions\OptimisticConcurrencyFailure;

final readonly class ExactlyAt implements ExpectedVersion
{
    public function __construct(
        private StreamVersion $version,
    ) {}

    #[\Override]
    public function assertCompatibleWith(StreamVersion $observed): void
    {
        if (!$observed->equalTo($this->version)) {
            throw new OptimisticConcurrencyFailure($this->version, $observed);
        }
    }
}
