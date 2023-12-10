<?php

declare(strict_types=1);

namespace Meteia\Events;

interface EventBus
{
    public function publishEvent(Event $event): void;

    public function registerEventHandler(string $eventClassName, string $eventHandlerClassName, callable $eventHandler): void;

    public function run(): void;
}
