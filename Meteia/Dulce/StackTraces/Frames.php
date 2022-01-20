<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

use Meteia\Application\ApplicationPath;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Throwable;

class Frames
{
    /**
     * @var FrameFilters
     */
    private $filters;

    /**
     * @var FileFragments
     */
    private $fileFragments;

    /**
     * @var ApplicationPath
     */
    private $applicationPath;

    public function __construct(FrameFilters $filters, FileFragments $fileFragments)
    {
        $this->filters = $filters;
        $this->fileFragments = $fileFragments;
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

        return array_map(function ($frame) {
            return $this->frame($frame['file'], $frame['line']);
        }, $frames);
    }

    private function rootCause(Throwable $throwable)
    {
        return $throwable->getPrevious() ? $this->rootCause($throwable->getPrevious()) : $throwable;
    }

    private function frame(string $file, int $line)
    {
        $path = new FilesystemPath($file);
        $fileFragment = $this->fileFragments->fileFragment($path, $line, 10);

        return new Frame($path, $line, $fileFragment);
    }
}
