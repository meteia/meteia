<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\Commands\CommandId;
use Meteia\Events\EventId;

readonly class ProcessId extends UniqueId
{
    #[\Override]
    public static function prefix(): string
    {
        return 'pid';
    }

    public static function fromCommandId(CommandId $commandId): self
    {
        return self::fromHex($commandId->hex());
    }

    public static function fromEventId(EventId $eventId): self
    {
        return self::fromHex($eventId->hex());
    }
}
