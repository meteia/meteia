<?php

declare(strict_types=1);

namespace Meteia\Database\EventHandlers\Files\FileUploaded;

use Meteia\Events\Event;
use Meteia\Events\EventHandler;
use Psr\Log\LoggerInterface;

final readonly class InsertIntoDatabase implements EventHandler
{
    public function __construct(
        private LoggerInterface $log,
    ) {}

    #[\Override]
    public function handle(Event $event): void
    {
        $this->log->info('File inserted into database', ['event' => $event::class]);
    }
}
