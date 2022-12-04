<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\Application\RepositoryPath;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

class DecoratedLog extends AbstractLogger
{
    private const SKIP_FRAMES = 1;

    private string $pathPrefix;

    public function __construct(
        private readonly LoggerInterface $log,
        readonly RepositoryPath $repositoryPath,
    ) {
        $this->pathPrefix = trim((string) $repositoryPath, DIRECTORY_SEPARATOR);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (!isset($context['file'], $context['line'])) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $frame = $trace[self::SKIP_FRAMES];
            $context['file'] = $frame['file'];
            $context['line'] = $frame['line'];
        }

        $context['source'] = str_replace($this->pathPrefix, '', $context['file'] ?? 'unknown');
        $context['source'] = trim($context['source'], DIRECTORY_SEPARATOR);
        $context['source'] .= ':' . $context['line'] ?? '0';

        unset($context['file'], $context['line']);
        $this->log->log($level, $message, $context);
    }
}
