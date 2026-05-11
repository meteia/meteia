<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\Fakes;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\EventTypeId;
use Override;

class FakeOccurred implements DomainEvent
{
    public function __construct(
        public string $publicData,
        protected string $protectedData,
        private string $privateData,
    ) {}

    #[Override]
    public static function eventTypeId(): EventTypeId
    {
        return EventTypeId::random();
    }
}
