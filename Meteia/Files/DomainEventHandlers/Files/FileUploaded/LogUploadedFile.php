<?php

declare(strict_types=1);

namespace Meteia\Files\DomainEventHandlers\Files\FileUploaded;

use Meteia\Domain\DomainEvent;
use Meteia\Domain\DomainEventHandler;
use Meteia\Files\DomainEvents\FileUploaded;
use Psr\Log\LoggerInterface;

readonly class LogUploadedFile implements DomainEventHandler
{
    public function __construct(
        private LoggerInterface $log,
    ) {
    }

    public function handle(DomainEvent|FileUploaded $event): void
    {
        $this->log->info('file', ['filename' => $event->filename]);
    }
}
