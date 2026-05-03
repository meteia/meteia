<?php

declare(strict_types=1);

namespace Meteia\WebAuthn\Contracts;

use Meteia\Events\Event;

interface EventDispatcher
{
    public function dispatch(Event $event): void;
}
