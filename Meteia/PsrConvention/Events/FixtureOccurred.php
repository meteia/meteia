<?php

declare(strict_types=1);

namespace Meteia\PsrConvention\Events;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\DerivedEventTypeId;

final readonly class FixtureOccurred implements DomainEvent
{
    use DerivedEventTypeId;
}
