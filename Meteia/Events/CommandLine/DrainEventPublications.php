<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use Meteia\Commands\Command;
use Meteia\Commands\CommandHandler;
use Meteia\Events\Commands\DrainEventPublications as DrainEventPublicationsCommand;
use Meteia\Events\OutboxPublisher;
use Override;
use Psr\Log\LoggerInterface;

/**
 * Drains the event publications outbox and attempts delivery to the bus.
 *
 * Run this periodically (via cron, supervisor, or the worker loop) to
 * achieve reliable at-least-once delivery of domain events to the message bus.
 *
 * @implements CommandHandler<DrainEventPublicationsCommand, void>
 */
final readonly class DrainEventPublications implements CommandHandler
{
    public function __construct(
        private OutboxPublisher $outboxPublisher,
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function handle(Command $command): void
    {
        \assert($command instanceof DrainEventPublicationsCommand);

        $published = $this->outboxPublisher->publishPending($command->limit);

        if ($published > 0) {
            $this->log->info('Event outbox drain completed', [
                'published' => $published,
                'limit' => $command->limit,
            ]);
        }
    }
}
