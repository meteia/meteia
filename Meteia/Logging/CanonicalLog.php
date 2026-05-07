<?php

declare(strict_types=1);

namespace Meteia\Logging;

class CanonicalLog
{
    private string $logLine = '';

    public function log(string|\Stringable $message, array $context = []): void
    {
    }

    public function toString(): string
    {
        return $this->logLine;
    }
}
