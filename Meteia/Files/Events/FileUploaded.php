<?php

declare(strict_types=1);

namespace Meteia\Files\Events;

use Meteia\Events\Event;

readonly class FileUploaded implements Event
{
    public function __construct(public string $filename)
    {
    }
}
