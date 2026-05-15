<?php

declare(strict_types=1);

namespace Meteia\Commands\Debug;

use Meteia\Application\Command as ApplicationCommand;
use Meteia\Commands\Command;

final readonly class Ping implements Command, ApplicationCommand
{
    public function __construct(
        public ?string $replyTo = null,
    ) {}
}
