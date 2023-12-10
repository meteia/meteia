<?php

declare(strict_types=1);

namespace Meteia\Events;

interface EventHandler
{
    public function handle(Event $event): void;
}
