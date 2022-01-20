<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\Domain\CommandMessages;
use Meteia\EventSourcing\EventMessages;

interface UnitOfWorkContext
{
    public function caused(EventMessages $eventMessages);

    public function wantsTo(CommandMessages $commandMessages);
}
