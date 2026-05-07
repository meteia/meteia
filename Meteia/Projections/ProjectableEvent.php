<?php

declare(strict_types=1);

namespace Meteia\Projections;

use Meteia\EventSourcing\RecordedEvent;

final readonly class ProjectableEvent
{
    public function __construct(
        private RecordedEvent $event,
        private GlobalSequence $position,
    ) {}

    public function event(): RecordedEvent
    {
        return $this->event;
    }

    public function position(): GlobalSequence
    {
        return $this->position;
    }
}
