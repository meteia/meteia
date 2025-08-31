<?php

declare(strict_types=1);

namespace Meteia\Database;

use Aura\Sql\Profiler\ProfilerInterface;
use Meteia\Performance\Timings;
use Psr\Log\LoggerInterface;

class ServerTimingProfiler implements ProfilerInterface
{
    private string $function;

    private float $startTime;

    public function __construct(
        private readonly Timings $timings,
    ) {}

    #[\Override]
    public function finish(?string $statement = null, array $values = []): void
    {
        $duration = microtime(true) - $this->startTime;
        $this->timings->add('app.sql.pdo.' . $this->function, $duration * 1000);
    }

    #[\Override]
    public function getLogFormat(): string
    {
        return '';
    }

    #[\Override]
    public function getLogLevel(): string
    {
    }

    #[\Override]
    public function getLogger(): LoggerInterface
    {
    }

    #[\Override]
    public function isActive(): bool
    {
        return true;
    }

    #[\Override]
    public function setActive(bool $active): void
    {
    }

    #[\Override]
    public function setLogFormat(string $logFormat): void
    {
    }

    #[\Override]
    public function setLogLevel(string $logLevel): void
    {
    }

    #[\Override]
    public function start(string $function): void
    {
        $this->function = $function;
        $this->startTime = microtime(true);
    }
}
