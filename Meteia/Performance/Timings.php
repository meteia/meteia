<?php

declare(strict_types=1);

namespace Meteia\Performance;

use function Meteia\Polyfills\array_map_assoc;

class Timings
{
    private $childDurations = 0;

    private $timeDepth = 0;

    private $timings = [];

    public function add($category, float $ms): void
    {
        $category = $this->filteredCategory($category);
        $this->timings[$category] = ($this->timings[$category] ?? 0) + $ms;
    }

    public function all(): array
    {
        return array_map_assoc(fn ($k, $v) => [$k => round($v, 4)], $this->timings);
    }

    public function measure(string $category, callable $c)
    {
        $category = $this->filteredCategory($category);
        ++$this->timeDepth;
        $startTime = hrtime(true);
        $result = $c();
        $endTime = hrtime(true);
        --$this->timeDepth;
        $duration = (($endTime - $startTime) / 1000000) - $this->childDurations;
        $this->childDurations += $duration;
        if ($this->timeDepth === 0) {
            $this->childDurations = 0;
        }

        $this->timings[$category] = ($this->timings[$category] ?? 0) + $duration;

        return $result;
    }

    private function filteredCategory(string $category): string
    {
        $category = trim($category);
        $category = str_replace('\\', '.', $category);

        return $category;
    }
}
