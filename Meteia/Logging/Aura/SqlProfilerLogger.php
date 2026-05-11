<?php

declare(strict_types=1);

namespace Meteia\Logging\Aura;

use Override;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

class SqlProfilerLogger extends AbstractLogger
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $statement = null;
        $rawStatement = $context['statement'] ?? null;
        if (\is_string($rawStatement) && $rawStatement !== '') {
            $lines = array_filter(array_map('trim', explode(PHP_EOL, $rawStatement)));
            $statement = implode(' ', $lines);
        }

        $duration = $context['duration'] ?? 0;
        $durationMs = \is_int($duration) || \is_float($duration) ? number_format($duration * 1000, 2) : '0.00';

        $shortContext = [
            'function' => $context['function'] ?? null,
            'durationMs' => $durationMs,
            'statement' => $statement,
            'values' => $context['values'] ?? null,
        ];

        $this->logger->log($level, 'Database Profile', $shortContext);
    }
}
