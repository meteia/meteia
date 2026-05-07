<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\ValueObjects\Identity\UniqueId;

interface EventMessageTarget
{
    public function handleEventMessage(UniqueId $streamId, DomainEvent $event, int $eventSequence): void;
}
