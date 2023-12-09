<?php

declare(strict_types=1);

namespace Meteia\Files\DomainEvents;

use Meteia\Domain\DomainEvent;

readonly class FileUploaded implements DomainEvent
{
    public function __construct(
        public string $filename,
    ) {
    }
}
