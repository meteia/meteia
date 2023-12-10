<?php

declare(strict_types=1);

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;

return [
    ProcessId::class => fn () => ProcessId::random(),
    CausationId::class => fn (ProcessId $processId) => CausationId::fromHex($processId->hex()),
    CorrelationId::class => fn (ProcessId $processId) => CorrelationId::fromHex($processId->hex()),
];
