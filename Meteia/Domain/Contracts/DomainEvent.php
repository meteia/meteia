<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\Events\Event;
use Meteia\EventSourcing\EventTypeId;

interface DomainEvent extends Event
{
    public static function eventTypeId(): EventTypeId;
}
