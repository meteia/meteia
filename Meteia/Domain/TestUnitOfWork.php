<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\EventSourcing\EventMessages;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

use function PHPUnit\Framework\assertEquals;

class TestUnitOfWork implements UnitOfWork
{
    private array $actual = [];

    public function __construct(
        private array $expected,
    ) {
        $this->eventMessages = new EventMessages();
    }

    #[\Override]
    public function caused(EventMessages $eventMessages): void
    {
        foreach ($eventMessages as $eventMessage) {
            $this->actual[] = \get_class($eventMessage->event);
        }
    }

    #[\Override]
    public function complete(CausationId $causationId, CorrelationId $correlationId): void
    {
        assertEquals($this->expected, $this->actual);
    }

    #[\Override]
    public function wantsTo(CommandMessages $commandMessages): void
    {
        foreach ($commandMessages as $msg) {
            $this->actual[] = $msg->command::class;
        }
    }
}
