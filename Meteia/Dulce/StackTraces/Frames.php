<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

use Meteia\ValueObjects\Identity\FilesystemPath;
use Throwable;

class Frames
{
    public function __construct(private readonly FrameFilters $filters, private readonly FileFragments $fileFragments)
    {
    }

    /**
     * @return Frame[]
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

        return array_map(fn ($frame) => $this->frame($frame['file'], $frame['line']), $frames);
    }

    private function frame(string $file, int $line): Frame
    {
        $path = new FilesystemPath($file);
        $fileFragment = $this->fileFragments->fileFragment($path, $line, 5);

        return new Frame($path, $line, $fileFragment);
    }

    private function rootCause(Throwable $throwable): Throwable
    {
        return $throwable->getPrevious() ? $this->rootCause($throwable->getPrevious()) : $throwable;
    }
}
