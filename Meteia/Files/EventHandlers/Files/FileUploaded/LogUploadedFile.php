<?php

declare(strict_types=1);

namespace Meteia\Files\EventHandlers\Files\FileUploaded;

use Meteia\Events\Event;
use Meteia\Events\EventHandler;
use Meteia\Files\Events\FileUploaded;
use Psr\Log\LoggerInterface;

readonly class LogUploadedFile implements EventHandler
{
    public function __construct(private LoggerInterface $log)
    {
    }

    public function handle(Event|FileUploaded $event): void
    {
        $this->log->info('File Uploaded', ['filename' => $event->filename]);
    }
}
