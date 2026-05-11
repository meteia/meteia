<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Override;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

class FanOut extends AbstractLogger
{
    /**
     * @param iterable<LoggerInterface> $logs
     */
    public function __construct(
        private iterable $logs,
    ) {}

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        foreach ($this->logs as $log) {
            $log->log($level, $message, $context);
        }
    }
}
