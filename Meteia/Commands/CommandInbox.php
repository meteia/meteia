<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandInbox
{
    public function subscribe(string $commandClassName, CommandMessageHandler $handler): void;

    public function run(): void;
}
