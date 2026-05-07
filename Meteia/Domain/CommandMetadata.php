<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\ValueObjects\AggregateRootId;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

final readonly class CommandMetadata
{
    public function __construct(
        public AggregateRootId $aggregateRootId,
        public CausationId $causationId,
        public CorrelationId $correlationId,
        public \DateTimeImmutable $issuedAt,
    ) {}
}
