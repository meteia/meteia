<?php

declare(strict_types=1);

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;

return [
    ProcessId::class => ProcessId::random(...),
    MessageScope::class => static fn(ProcessId $processId): MessageScope => new MessageScope(
        CorrelationId::fromHex($processId->hex()),
        CausationId::fromHex($processId->hex()),
        $processId,
    ),
];
