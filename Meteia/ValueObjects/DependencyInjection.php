<?php

declare(strict_types=1);

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;

return [
    ProcessId::class => static fn () => ProcessId::random(),
    CausationId::class => static fn (ProcessId $processId) => CausationId::fromHex($processId->hex()),
    CorrelationId::class => static fn (ProcessId $processId) => CorrelationId::fromHex($processId->hex()),
];
