<?php

declare(strict_types=1);

namespace Meteia\Database\EventHandlers\Files\FileUploaded;

use Meteia\Events\Event;
use Meteia\Events\EventHandler;
use Meteia\Files\Events\FileUploaded;
use Psr\Log\LoggerInterface;

class InsertIntoDatabase implements EventHandler
{
    public function __construct(
        private LoggerInterface $log,
    ) {}

    #[\Override]
    public function handle(Event|FileUploaded $event): void
    {
        $this->log->info('File inserted into database', ['filename' => $event->filename]);
    }
}
