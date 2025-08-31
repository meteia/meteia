<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\Commands\CommandId;

readonly class CausationId extends UniqueId
{
    #[\Override]
    public static function prefix(): string
    {
        return 'cus';
    }

    public static function fromCommandId(CommandId $commandId): self
    {
        return self::fromHex($commandId->hex());
    }
}
