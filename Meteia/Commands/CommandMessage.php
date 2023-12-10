<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

readonly class CommandMessage
{
    public function __construct(
        public Command $command,
        public CausationId $causationId,
        public CorrelationId $correlationId,
    ) {
    }
}
