<?php

declare(strict_types=1);

namespace Meteia\Dulce\Templates;

use Meteia\Application\RepositoryPath;
use Meteia\Bluestone\Contracts\Renderable;
use Meteia\Bluestone\PhpTemplate;

use function Meteia\Polyfills\without_prefix;

class StackTrace implements Renderable
{
    use PhpTemplate;

    private \Throwable $throwable;

    public function __construct(
        private RepositoryPath $repositoryPath,
    ) {
    }

    public function for(\Throwable $throwable): self
    {
        $copy = clone $this;
        $copy->throwable = $throwable;

        return $copy;
    }

    /**
     * @return StackFrame[]
     */
    public function stackFrames(): array
    {
        $frames = $this->throwable->getTrace();
        $frames[0]['file'] = $this->throwable->getFile();
        $frames[0]['line'] = $this->throwable->getLine();

        $frames = array_filter($frames, function ($frame) {
            return !isset($frame['file']) || stripos($frame['file'], 'vendor') === false;
        });

        return array_map(function ($trace) {
            if (!isset($trace['file'], $trace['line'])) {
                return new MissingFrame();
            }
            $relative = without_prefix($trace['file'], (string) $this->repositoryPath . '/');

            return new StackFrame($trace['file'], $trace['line'], $relative);
        }, $frames);
    }
}
