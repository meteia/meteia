<?php

declare(strict_types=1);

namespace Meteia\Events;

interface EventOutbox
{
    public function publish(Event $event): void;
}
