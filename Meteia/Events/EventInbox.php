<?php

declare(strict_types=1);

namespace Meteia\Events;

interface EventInbox
{
    public function subscribe(string $eventClassName, string $eventHandlerClassName, callable $eventHandler): void;

    public function run(): void;
}
