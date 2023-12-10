<?php

declare(strict_types=1);

namespace Meteia\Files\Commands;

use Meteia\Commands\Command;
use Meteia\Events\EventBus;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\Events\FileUploaded;
use Psr\Log\LoggerInterface;

readonly class DeleteFile implements Command
{
    public function __construct(
        public string $path,
    ) {
    }

    public function invoke(LoggerInterface $log, Storage $storage, EventBus $eventBus): void
    {
        $log->info('Deleting file', ['path' => $this->path]);
        $storage->delete($this->path);
        $eventBus->publishEvent(new FileUploaded($this->path));
    }
}
