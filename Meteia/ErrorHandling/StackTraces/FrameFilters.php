<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\StackTraces;

class FrameFilters
{
    /**
     * @var array<int, FrameFilter>
     */
    private array $filters;

    /**
     * @param FrameFilter[] $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = array_map(static fn (FrameFilter $filter) => $filter, $filters);
    }

    public function filtered(array $frames): array
    {
        return array_filter($frames, fn ($frame) => $this->filter($frame));
    }

    private function filter(array $frame): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filter($frame)) {
                return false;
            }
        }

        return true;
    }
}
