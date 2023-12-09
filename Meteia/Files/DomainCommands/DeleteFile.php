<?php

declare(strict_types=1);

namespace Meteia\Files\DomainCommands;

use Meteia\Domain\DomainCommand;
use Meteia\Files\Contracts\Storage;
use Psr\Log\LoggerInterface;

readonly class DeleteFile implements DomainCommand
{
    public function __construct(
        public string $path,
    ) {
    }

    public function invoke(LoggerInterface $log, Storage $storage): void
    {
        $log->info('Deleting file', ['path' => $this->path]);
        $storage->delete($this->path);
    }
}
