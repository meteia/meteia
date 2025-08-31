<?php

declare(strict_types=1);

namespace Meteia\Logging\Aura;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class SqlProfilerLogger extends AbstractLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[\Override]
    public function log($level, $message, array $context = []): void
    {
        if ($context['statement']) {
            $lines = array_map('trim', explode(PHP_EOL, $context['statement']));
            $lines = array_filter($lines);
            $statement = implode(' ', $lines);
        }
        $shortContext = [
            'function' => $context['function'],
            'durationMs' => number_format($context['duration'] * 1000, 2),
            'statement' => $statement ?? null,
            'values' => $context['values'] ?? null,
        ];

        $this->logger->log($level, 'Database Profile', $shortContext);
    }
}
