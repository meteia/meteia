<?php

declare(strict_types=1);

namespace Meteia\Debug\Commands;

use Meteia\Commands\Command;

final readonly class Ping implements Command
{
    public function __construct(
        public ?string $replyTo = null,
    ) {}
}
