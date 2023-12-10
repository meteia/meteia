<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\Application\RepositoryPath;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class DecoratedLog extends AbstractLogger
{
    private const SKIP_FRAMES = 1;

    private string $pathPrefix;

    public function __construct(
        readonly private LoggerInterface $log,
        readonly private CorrelationId $correlationId,
        readonly private CausationId $causationId,
        readonly private ProcessId $processId,
        RepositoryPath $repositoryPath,
    ) {
        $this->pathPrefix = trim((string) $repositoryPath, \DIRECTORY_SEPARATOR);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (!isset($context['file'], $context['line'])) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $frame = $trace[self::SKIP_FRAMES];
            $context['file'] = $frame['file'];
            $context['line'] = $frame['line'];
        }

        $context['source'] = str_replace($this->pathPrefix, '', $context['file'] ?? 'unknown');
        $context['source'] = trim($context['source'], \DIRECTORY_SEPARATOR);
        $context['source'] .= ':' . $context['line'] ?? '0';

        unset($context['file'], $context['line']);
        $this->log->log($level, implode(' -> ', [
            $this->correlationId,
            $this->causationId,
            $this->processId,
            $message,
        ]), $context);
    }
}
