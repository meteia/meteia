<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandBus
{
    public function publishCommand(Command $command): void;

    public function registerCommandHandler(string $commandClassName, CommandMessageHandler $handler): void;

    public function run(): void;
}
