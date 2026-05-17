<?php

declare(strict_types=1);

namespace Meteia\TypeScript\Fixtures\EventPayloads;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\DerivedEventTypeId;

final readonly class StudentRegistered implements DomainEvent
{
    use DerivedEventTypeId;

    public function __construct(
        public string $studentName,
    ) {}
}
