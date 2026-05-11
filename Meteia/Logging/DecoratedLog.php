<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\Bootstrap\RepositoryPath;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Override;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

class DecoratedLog extends AbstractLogger
{
    private const SKIP_FRAMES = 1;

    private string $pathPrefix;

    public function __construct(
        private readonly LoggerInterface $log,
        private readonly MessageScopeSource $scopeSource,
        RepositoryPath $repositoryPath,
    ) {
        $this->pathPrefix = trim((string) $repositoryPath, \DIRECTORY_SEPARATOR);
    }

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (!isset($context['file'], $context['line'])) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $frame = $trace[self::SKIP_FRAMES];
            $context['file'] = $frame['file'] ?? 'unknown';
            $context['line'] = $frame['line'] ?? 0;
        }

        $file = \is_string($context['file']) ? $context['file'] : 'unknown';
        $line = \is_scalar($context['line']) ? (string) $context['line'] : '0';
        $source = trim(str_replace($this->pathPrefix, '', $file), \DIRECTORY_SEPARATOR);
        $context['source'] = $source . ':' . $line;

        unset($context['file'], $context['line']);

        $scope = $this->scopeSource->current();
        $this->log->log(
            $level,
            implode(' -> ', [
                $scope->correlationId(),
                $scope->causationId(),
                $scope->processId(),
                $message,
            ]),
            $context,
        );
    }
}
