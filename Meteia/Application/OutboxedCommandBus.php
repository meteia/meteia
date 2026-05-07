<?php

declare(strict_types=1);

namespace Meteia\Application;

use Meteia\Commands\Command as TransportCommand;
use Meteia\Commands\CommandOutbox;

/**
 * Publishes the application command to the AMQP `CommandOutbox`, deferring execution to a worker.
 * The command MUST also implement `Meteia\Commands\Command` (the AMQP transport marker) so that
 * inboxes know how to consume it.
 */
final readonly class OutboxedCommandBus implements CommandBus
{
    public function __construct(
        private CommandOutbox $outbox,
    ) {}

    #[\Override]
    public function dispatch(Command $command): CommandResult
    {
        if (!$command instanceof TransportCommand) {
            return new Rejected(\sprintf(
                '`%s` must also implement `%s` for transport via the outbox.',
                $command::class,
                TransportCommand::class,
            ));
        }

        $this->outbox->publish($command);

        return new Accepted();
    }
}
