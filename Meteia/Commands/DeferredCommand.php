<?php

declare(strict_types=1);

namespace Meteia\Commands;

final readonly class DeferredCommand
{
    public function __construct(
        private CommandId $commandId,
    ) {}

    public function commandId(): CommandId
    {
        return $this->commandId;
    }
}
