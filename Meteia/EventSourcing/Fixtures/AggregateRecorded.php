<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Fixtures;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\EventTypeId;
use Override;

/**
 * @internal
 */
final readonly class AggregateRecorded implements DomainEvent
{
    #[Override]
    public static function eventTypeId(): EventTypeId
    {
        return EventTypeId::random();
    }
}
