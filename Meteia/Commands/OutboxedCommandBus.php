<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Override;

/**
 * Publishes the command to the AMQP `CommandOutbox`, deferring execution to a worker.
 */
final readonly class OutboxedCommandBus implements CommandBus
{
    public function __construct(
        private CommandOutbox $outbox,
    ) {}

    #[Override]
    public function dispatch(Command $command): void
    {
        $this->outbox->publish($command);
    }
}
