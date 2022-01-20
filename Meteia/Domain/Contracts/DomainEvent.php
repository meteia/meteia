<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\EventSourcing\EventTypeId;

interface DomainEvent
{
    public static function eventTypeId(): EventTypeId;
}
