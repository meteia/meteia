<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\Domain\PendingCommands;
use Meteia\EventSourcing\PendingEvents;

interface UnitOfWorkContext
{
    public function caused(PendingEvents $events): void;

    public function wantsTo(PendingCommands $commands): void;
}
