<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;

interface EventBusWorker
{
    public function __invoke(
        Event $event,
        EventId $eventId,
        CorrelationId $correlationId,
        CausationId $causationId,
        ProcessId $processId,
    ): void;
}
