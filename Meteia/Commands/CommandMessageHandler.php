<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;

interface CommandMessageHandler
{
    public function handle(
        Command $command,
        CommandId $commandId,
        CorrelationId $correlationId,
        CausationId $causationId,
        ProcessId $processId,
    ): void;
}
