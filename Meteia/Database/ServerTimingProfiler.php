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

    public function __construct(private readonly Timings $timings)
    {
    }

    public function finish(?string $statement = null, array $values = []): void
    {
        $duration = microtime(true) - $this->startTime;
        $this->timings->add('app.sql.pdo.' . $this->function, $duration * 1000);
    }

    public function getLogFormat(): string
    {
        return '';
    }

    public function getLogLevel(): string
    {
    }

    public function getLogger(): LoggerInterface
    {
    }

    public function isActive(): bool
    {
        return true;
    }

    public function setActive(bool $active)
    {
    }

    public function setLogFormat(string $logFormat): void
    {
    }

    public function setLogLevel(string $logLevel): void
    {
    }

    public function start(string $function): void
    {
        $this->function = $function;
        $this->startTime = microtime(true);
    }
}
