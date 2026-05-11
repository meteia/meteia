<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\Templates;

use Meteia\Bluestone\PhpTemplate;
use Meteia\Bootstrap\RepositoryPath;
use Stringable;
use Throwable;

use function Meteia\Polyfills\without_prefix;

class StackTrace implements Stringable
{
    use PhpTemplate;

    private Throwable $throwable;

    public function __construct(
        private readonly RepositoryPath $repositoryPath,
    ) {}

    public function for(Throwable $throwable): self
    {
        $copy = clone $this;
        $copy->throwable = $throwable;

        return $copy;
    }

    /**
     * @return list<MissingFrame|StackFrame>
     */
    public function stackFrames(): array
    {
        $frames = $this->throwable->getTrace();
        if (isset($frames[0]) && \is_array($frames[0])) {
            $frames[0]['file'] = $this->throwable->getFile();
            $frames[0]['line'] = $this->throwable->getLine();
        }

        $frames = array_filter(
            $frames,
            static fn($frame) => !isset($frame['file']) || !stripos((string) $frame['file'], 'vendor'),
        );

        return array_values(array_map(function ($trace) {
            if (!isset($trace['file'], $trace['line'])) {
                return new MissingFrame();
            }
            $file = (string) $trace['file'];
            $line = (int) $trace['line'];
            $relative = without_prefix($file, (string) $this->repositoryPath . '/');

            return new StackFrame($file, $line, $relative);
        }, $frames));
    }
}
