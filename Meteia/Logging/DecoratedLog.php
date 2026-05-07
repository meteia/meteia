<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\Bootstrap\RepositoryPath;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

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

    #[\Override]
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
