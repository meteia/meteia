<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\ValueObjects\Identity\MessageScope;

final readonly class CommandDelivery
{
    public function __construct(
        private CommandId $commandId,
        private Command $command,
        private MessageScope $scope,
    ) {}

    public function commandId(): CommandId
    {
        return $this->commandId;
    }

    public function command(): Command
    {
        return $this->command;
    }

    public function scope(): MessageScope
    {
        return $this->scope;
    }
}
