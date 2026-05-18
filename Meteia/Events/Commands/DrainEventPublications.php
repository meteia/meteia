<?php

declare(strict_types=1);

namespace Meteia\Events\Commands;

use Meteia\Commands\Command;

/**
 * @implements Command<void>
 */
final readonly class DrainEventPublications implements Command
{
    public function __construct(
        public int $limit = 100,
    ) {}
}
