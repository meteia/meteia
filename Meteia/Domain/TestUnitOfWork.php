<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\PendingEvents;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;

use function PHPUnit\Framework\assertEquals;

class TestUnitOfWork implements UnitOfWork
{
    /** @var list<class-string> */
    private array $actual = [];

    /**
     * @param list<class-string> $expected
     */
    public function __construct(
        private array $expected,
    ) {}

    #[Override]
    public function caused(PendingEvents $events): void
    {
        /** @var PendingEvent $pending */
        foreach ($events as $pending) {
            $this->actual[] = $pending->event()::class;
        }
    }

    #[Override]
    public function complete(MessageScope $scope): void
    {
        assertEquals($this->expected, $this->actual);
    }

    #[Override]
    public function wantsTo(PendingCommands $commands): void
    {
        /** @var PendingCommand $pending */
        foreach ($commands as $pending) {
            $this->actual[] = $pending->command()::class;
        }
    }
}
