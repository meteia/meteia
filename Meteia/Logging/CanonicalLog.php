<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Stringable;

class CanonicalLog
{
    private string $logLine = '';

    public function log(Stringable|string $message, array $context = [])
    {
    }

    public function toString(): string
    {
        return $this->logLine;
    }
}
