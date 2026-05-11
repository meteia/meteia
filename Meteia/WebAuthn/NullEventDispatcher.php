<?php

declare(strict_types=1);

namespace Meteia\WebAuthn;

use Meteia\Events\Event;
use Meteia\WebAuthn\Contracts\EventDispatcher;
use Override;

class NullEventDispatcher implements EventDispatcher
{
    #[Override]
    public function dispatch(Event $event): void {}
}
