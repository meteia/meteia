<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\StackTraces;

use Meteia\ValueObjects\Identity\FilesystemPath;
use Throwable;

class Frames
{
    public function __construct(
        private readonly FrameFilters $filters,
        private readonly FileFragments $fileFragments,
    ) {}

    /**
     * @return list<Frame>
     */
    public function from(Throwable $throwable): array
    {
        $throwable = $this->rootCause($throwable);
        $frames = $throwable->getTrace();
        array_unshift($frames, [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ]);
        $frames = $this->filters->filtered($frames);

        return array_values(array_map(fn($frame) => $this->frame(
            (string) ($frame['file'] ?? ''),
            (int) ($frame['line'] ?? 0),
        ), $frames));
    }

    private function frame(string $file, int $line): Frame
    {
        $path = new FilesystemPath($file);
        $fileFragment = $this->fileFragments->fileFragment($path, $line, 5);

        return new Frame($path, $line, $fileFragment);
    }

    private function rootCause(Throwable $throwable): Throwable
    {
        $previous = $throwable->getPrevious();

        return $previous !== null ? $this->rootCause($previous) : $throwable;
    }
}
