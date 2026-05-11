<?php

declare(strict_types=1);

namespace Meteia\Database;

use Aura\Sql\Profiler\ProfilerInterface;
use Meteia\Performance\Timings;
use Override;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

final class ServerTimingProfiler implements ProfilerInterface
{
    private bool $active = true;

    private string $function = '';

    private string $logFormat = '';

    private string $logLevel = LogLevel::DEBUG;

    private float $startTime = 0.0;

    public function __construct(
        private readonly Timings $timings,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    #[Override]
    public function finish(?string $statement = null, array $values = []): void
    {
        if (!$this->active || $this->startTime === 0.0) {
            return;
        }

        $duration = microtime(true) - $this->startTime;
        $this->timings->add('app.sql.pdo.' . $this->function, $duration * 1000);
        $this->startTime = 0.0;
    }

    #[Override]
    public function getLogFormat(): string
    {
        return $this->logFormat;
    }

    #[Override]
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    #[Override]
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->active;
    }

    #[Override]
    public function setActive($active): void
    {
        $this->active = $active;
    }

    #[Override]
    public function setLogFormat(string $logFormat): void
    {
        $this->logFormat = $logFormat;
    }

    #[Override]
    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    #[Override]
    public function start(string $function): void
    {
        if (!$this->active) {
            return;
        }

        $this->function = $function;
        $this->startTime = microtime(true);
    }
}
