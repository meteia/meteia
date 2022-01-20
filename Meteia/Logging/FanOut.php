<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class FanOut extends AbstractLogger
{
    public function __construct(private array $logs)
    {
    }

    public function log($level, $message, array $context = []): void
    {
        /** @var LoggerInterface $log */
        foreach ($this->logs as $log) {
            $log->log($level, $message, $context);
        }
    }
}
